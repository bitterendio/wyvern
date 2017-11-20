if (typeof window.config === 'undefined') {
  var json = require('./wyvernConfig.json');
  window.config = {
    base_url: json.base_url,
    root: json.root
  }
}