if (typeof window.config === 'undefined') {
  const json = require('./wyvernConfig.json');
  window.config = {
    base_url: json.base_url,
    root: json.root
  }
}