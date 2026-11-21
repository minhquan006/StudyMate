const fs = require('node:fs');
const path = require('node:path');

// Call only after signing in to the disposable Student fixture (course 2).
async function verifyLessonOutline({page, base, check, artifacts, expectBroken = false}) {
  if (!['localhost', '127.0.0.1'].includes(new URL(base).hostname)) {
    throw new Error('Lesson outline checks require a disposable loopback app.');
  }
  const report = {expectedBroken: expectBroken, samples: []};
  const sizes = expectBroken
    ? [[834, 600], [991, 600]]
    : [...[390, 767, 768, 834, 991, 992, 1366].map(width => [width, 600]),
       [834, 500], [991, 500], [834, 900], [991, 900]];
  const postActions = [];
  const recordPost = request => {
    if (request.method() === 'POST') {
      const data = new URLSearchParams(request.postData() || '');
      if (data.get('action') === 'last_lesson') {
        postActions.push({course: data.get('course_id'), lesson: data.get('lesson_id')});
      }
    }
  };
  page.on('request', recordPost);
  try {
    for (const language of expectBroken ? ['vi'] : ['vi', 'en']) {
      for (const theme of expectBroken ? ['light'] : ['light', 'dark']) {
        for (const [width, height] of sizes) {
          const label = `outline ${language}/${theme}/${width}x${height}`;
          await page.setViewportSize({width, height});
          await page.goto(`${base}?page=learn&id=2&lesson=8&ui_language=${language}&ui_theme=${theme}`);
          await page.evaluate(() => document.fonts.ready);
          const outline = page.locator('#course-outline');
          const button = outline.locator('form').filter({
            has: page.locator('input[name=lesson_id][value="7"]')
          }).locator('button.lesson-link');
          await page.evaluate(() => scrollTo({top: 650, behavior: 'instant'}));
          await outline.evaluate(el => el.scrollTo({top: el.scrollHeight, behavior: 'instant'}));
          if (width < 768) {
            await button.evaluate(el => el.scrollIntoView({block: 'center', behavior: 'instant'}));
          } else {
            // On a short screen the first row can be outside the outline's own
            // scroll viewport. Reveal it within that viewport only; do not move
            // the page or compensate for the header that caused the regression.
            await button.evaluate(el => {
              const outline = document.querySelector('#course-outline');
              const r = el.getBoundingClientRect(), o = outline.getBoundingClientRect();
              if (r.top < o.top || r.bottom > o.bottom) {
                outline.scrollBy({top: r.top - o.top - 8, behavior: 'instant'});
              }
            });
          }
          await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
          const sample = await button.evaluate(el => {
            const r = el.getBoundingClientRect();
            const outline = document.querySelector('#course-outline');
            const o = outline.getBoundingClientRect();
            const rail = outline.closest('.learning-rail') || outline;
            const railBounds = rail.getBoundingClientRect();
            const h = document.querySelector('#site-header').getBoundingClientRect();
            const x = r.left + r.width / 2, y = r.top + r.height / 2;
            const target = document.elementFromPoint(x, y);
            return {
              scrollY, innerScroll: outline.scrollTop,
              scrollHeight: outline.scrollHeight, clientHeight: outline.clientHeight,
              position: getComputedStyle(rail).position,
              outlineTop: railBounds.top, outlineBottom: railBounds.bottom,
              innerOutlineTop: o.top, innerOutlineBottom: o.bottom, headerBottom: h.bottom,
              buttonTop: r.top, buttonBottom: r.bottom, x, y,
              receivesClick: target === el || el.contains(target),
              hitClass: target?.getAttribute('class'),
              overflow: document.documentElement.scrollWidth > innerWidth + 1
            };
          });
          report.samples.push({language, theme, width, height, ...sample});
          if (expectBroken) {
            check(sample.scrollY >= 600 && sample.innerScroll > 0, `${label}: reproduces both scrolls`);
            check(!sample.receivesClick && sample.buttonTop < sample.headerBottom, `${label}: header intercepts lesson choice`);
          } else {
            check(!sample.overflow, `${label}: fits viewport`);
            check(width < 768 ? sample.position === 'static'
              : sample.position === 'sticky' && sample.outlineTop >= sample.headerBottom && sample.outlineBottom <= height + 1,
              `${label}: outline remains usable below header or in mobile flow`);
            check(width < 768 || height > 600 || sample.scrollY >= 600 && sample.innerScroll > 0,
              `${label}: exercises page and inner outline scrolling`);
            check(sample.receivesClick && sample.buttonTop >= sample.headerBottom && sample.buttonBottom <= height,
              `${label}: lesson choice receives pointer below header`);
          }
          if (language === 'vi' && theme === 'light' && height === 600 && [834, 991].includes(width)) {
            await page.screenshot({path: path.join(artifacts, `outline-${expectBroken ? 'before' : 'after'}-${width}.png`), animations: 'disabled'});
          }
          postActions.length = 0;
          const navigation = page.waitForURL(url => url.searchParams.get('lesson') === '7', {
            timeout: expectBroken ? 1200 : 10000
          }).then(() => true, () => false);
          // Coordinate click intentionally avoids locator auto-scroll around the header.
          await page.mouse.click(sample.x, sample.y);
          const navigated = await navigation;
          if (expectBroken) {
            check(!navigated && postActions.length === 0, `${label}: intercepted click does not submit or change lesson`);
          } else {
            check(postActions.length === 1 && postActions[0].course === '2' && postActions[0].lesson === '7',
              `${label}: coordinate click submits the requested lesson once`);
            check(navigated && await page.locator('#course-outline button[aria-current="step"]').count() === 1,
              `${label}: navigation selects the requested lesson`);
            check(await page.locator('.mobile-menu-toggle').getAttribute('aria-expanded') === 'false',
              `${label}: lesson click leaves navigation closed`);
          }
        }
      }
    }
  } catch (error) {
    report.failure = error.stack;
    throw error;
  } finally {
    page.off('request', recordPost);
    fs.writeFileSync(path.join(artifacts, 'QA_LESSON_OUTLINE.json'), JSON.stringify(report, null, 2) + '\n');
  }
}

module.exports = {verifyLessonOutline};
