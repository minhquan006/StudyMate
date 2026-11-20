"""HTTP ID regressions against the runner's disposable database."""


def verify_management_ids(php, snapshot, admin, owner, check):
    def rejected(label, client, action, data, status=422):
        before = snapshot()
        actual = client.post(action, **data)[0]
        check(actual == status, 'ID validation: ' + label + ' returns ' + str(status))
        check(snapshot() == before, 'ID validation: ' + label + ' preserves all tables')

    def invalid_ids(field, valid_id):
        return [
            ('array', {field + '[]': '99999'}),
            ('nested array', {field + '[id]': str(valid_id)}),
            ('alphabetic', {field: 'abc'}),
            ('numeric prefix', {field: str(valid_id) + 'abc'}),
            ('empty', {field: ''}),
            ('zero', {field: '0'}),
            ('negative', {field: '-1'}),
            ('decimal', {field: '1.5'}),
            ('scientific notation', {field: '1e0'}),
            ('overflow', {field: '9' * 40}),
        ]

    # The two category_save cases from the review, plus invalid boundaries.
    for label, payload in invalid_ids('category_id', 1):
        rejected('category_save ' + label, admin, 'category_save',
                 dict(name='Invalid category ID must not write', **payload))

    check(admin.post('category_save', name='ID validation category')[0] == 303,
          'ID validation: category_id absent creates a category')
    category = int(php("echo val(\"SELECT id FROM categories WHERE name='ID validation category'\");"))
    check(category > 0, 'ID validation: created category is persisted')
    check(admin.post('category_save', category_id=category, name='ID validation renamed')[0] == 303,
          'ID validation: positive category_id updates its row')
    check(php("echo val('SELECT name FROM categories WHERE id=" + str(category) + "');") == 'ID validation renamed',
          'ID validation: category update persisted')

    status, headers, _ = owner.post('course_save', course_id=0, title='ID validation draft',
                                    description='Disposable course for ID mutation regression checks.',
                                    category_id=1, cover_key='database')
    check(status == 303, 'ID validation: course_id zero retains the create form contract')
    from urllib.parse import parse_qs, urlparse
    course = int(parse_qs(urlparse(headers['Location']).query)['id'][0])
    check(owner.post('chapter_save', course_id=course, title='ID validation chapter', position=1)[0] == 303,
          'ID validation: chapter_id absent creates a chapter')
    chapter = int(php("echo val('SELECT id FROM chapters WHERE course_id=" + str(course) + "');"))
    check(owner.post('lesson_save', course_id=course, lesson_id=0, chapter_id=chapter,
                     title='ID validation lesson', body='Public regression lesson content. ' * 3,
                     position=1, youtube_video_id='')[0] == 303,
          'ID validation: lesson_id zero retains the create form contract')
    lesson = int(php("echo val('SELECT id FROM lessons WHERE chapter_id=" + str(chapter) + "');"))

    # A valid draft makes the numeric-prefix delete regression destructive on old code.
    for label, payload in invalid_ids('chapter_id', chapter):
        rejected('chapter_delete ' + label, owner, 'chapter_delete',
                 dict(course_id=course, confirm=1, **payload))
    rejected('chapter_delete missing', owner, 'chapter_delete', dict(course_id=course, confirm=1))
    rejected('chapter_delete missing row', owner, 'chapter_delete',
             dict(course_id=course, chapter_id=99999, confirm=1), 404)
    rejected('category_save missing row', admin, 'category_save',
             dict(category_id=99999, name='ID validation absent row'), 404)

    # The same reader protects related IDs without changing existing valid forms.
    related = [
        (admin, 'category_delete', 'category_id', category, {'confirm': 1}, False),
        (admin, 'user_status', 'user_id', 2, {'is_active': 0, 'confirm': 1}, False),
        (owner, 'course_save', 'course_id', course,
         {'title': 'ID validation update', 'description': 'Valid description for a rejected ID update.', 'category_id': 1}, True),
        (owner, 'course_save', 'category_id', 1,
         {'course_id': course, 'title': 'ID validation update', 'description': 'Valid description for a rejected category ID.'}, False),
        (owner, 'chapter_save', 'chapter_id', chapter,
         {'course_id': course, 'title': 'ID validation chapter update', 'position': 1}, False),
        (owner, 'chapter_delete', 'course_id', course, {'chapter_id': chapter, 'confirm': 1}, False),
        (owner, 'lesson_save', 'lesson_id', lesson,
         {'course_id': course, 'chapter_id': chapter, 'title': 'Valid lesson', 'body': 'Valid lesson content. ' * 4, 'position': 1}, True),
        (owner, 'lesson_save', 'chapter_id', chapter,
         {'course_id': course, 'lesson_id': 0, 'title': 'Valid lesson', 'body': 'Valid lesson content. ' * 4, 'position': 1}, False),
        (owner, 'lesson_delete', 'lesson_id', lesson, {'course_id': course, 'confirm': 1}, False),
    ]
    for client, action, field, valid_id, fields, zero_allowed in related:
        for label, payload in invalid_ids(field, valid_id):
            if label not in ['array', 'numeric prefix', 'zero'] or (zero_allowed and label == 'zero'):
                continue
            rejected(action + ' ' + field + ' ' + label, client, action, dict(fields, **payload))

    for client, action, fields, label in [
        (admin, 'category_delete', {'confirm': 1}, 'category_delete missing'),
        (admin, 'user_status', {'is_active': 0, 'confirm': 1}, 'user_status missing'),
        (owner, 'lesson_delete', {'course_id': course, 'confirm': 1}, 'lesson_delete missing'),
        (owner, 'chapter_delete', {'chapter_id': chapter, 'confirm': 1}, 'course_id missing'),
    ]:
        rejected(label, client, action, fields)

    check(owner.post('lesson_delete', course_id=course, lesson_id=lesson, confirm=1)[0] == 303,
          'ID validation: valid lesson deletion works')
    check(owner.post('chapter_delete', course_id=course, chapter_id=chapter, confirm=1)[0] == 303,
          'ID validation: valid chapter deletion works')
    check(php('echo (int)val(\'SELECT COUNT(*) FROM chapters WHERE id=' + str(chapter) + '\');') == '0',
          'ID validation: exactly the valid chapter was deleted')
    check(owner.post('course_delete', course_id=course, confirm=1)[0] == 303,
          'ID validation: disposable course cleanup works')
    check(admin.post('category_delete', category_id=category, confirm=1)[0] == 303,
          'ID validation: valid category deletion works')
