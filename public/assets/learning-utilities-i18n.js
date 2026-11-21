// Loaded before the optional widgets. PHP renders the same language catalog.
(() => {
  const messages = JSON.parse(document.getElementById('learning-utilities-i18n')?.dataset.messages || '{}');
  window.StudyMateUtilities = {
    locale: document.documentElement.lang === 'en' ? 'en-US' : 'vi-VN',
    t(message, values = {}) {
      let text = messages[message.replace(/\r\n/g, '\n')] ?? message;
      for (const [key, value] of Object.entries(values)) text = text.replaceAll('{' + key + '}', String(value));
      return text;
    }
  };
})();
