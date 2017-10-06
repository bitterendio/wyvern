const fs = require('fs');
const path = require('path');
const readline = require('readline');

const wyvernConfig = path.join(__dirname, 'wyvernConfig.json');

module.exports = function(callback) {
  if (fs.existsSync(wyvernConfig)) {
    const file = JSON.parse(fs.readFileSync(wyvernConfig, 'utf8'));
    if (!file.base_url || !file.root) {
      const rl = readline.createInterface({
        input: process.stdin,
        output: process.stdout,
      });
      console.log('\x1b[31m\x1b[1mYou don\'t have your development URL in config file!\x1b[0m');
      console.log('/build/wyvernConfig.json\n');
      rl.question('Please fill your development URL: (i.e. example.dev) ', function(answer) {
        let baseUrl = answer;
        if (answer.indexOf('http://') === -1) {
          baseUrl = `http://${answer}`;
        }
        file.base_url = baseUrl;
        rl.question(
          `Please fill root URL of Wordpress REST API: (${baseUrl}/wp-json) `,
          function (secondAnswer) {
            let root = secondAnswer;
            if (!secondAnswer) {
              root = `${baseUrl}/wp-json`;
            }
            file.root = root;
            rl.close();
          }
        );
      });
      rl.on('close', function() {
        fs.writeFileSync(wyvernConfig, JSON.stringify(file, null, 4));
        callback();
      });
    } else {
      callback();
    }
  }
};
