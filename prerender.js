const nightmare = require('nightmare')({
  switches: {
    'ignore-certificate-errors': true
  },
  show: true
});
const fs = require('fs');
const axios = require('axios');
const path = require('path');

const configPath = './build/wyvernConfig.json';

function mkpathIfNotExists(filePath) {
  const dirname = path.dirname(filePath);
  if (fs.existsSync(dirname)) {
    return true;
  }
  mkpathIfNotExists(dirname);
  fs.mkdirSync(dirname);
}

// Check config
if (!fs.existsSync(configPath)) {
  throw new Error(`Wyvern is not configured - check your ${configPath} or use npm run dev to configure via CLI`);
}

// Read config
const config = require(configPath);

// Get config and available routes
axios.get(`${config.root}/wyvern/v1/config`)
    .then((response) => {
      response.data.routes.reduce((accumulator, route) => {
        return accumulator.then((results) => {
          const url = `${config.base_url}${route.path.replace(config.base_url, '')}`;
          const relative = url.replace(config.base_url, '');
          return nightmare
            .goto(url)
            .wait(5000)
            .evaluate(() =>
                document.querySelector('#app').innerHTML
            )
            .then((content) => {
              // Push result to results array
              results.push({
                relative,
                content
              });
              // Write file with contents
              const filepath = `prerender${relative}index.html`;
              mkpathIfNotExists(filepath);
              fs.writeFileSync(filepath, content);
              // Return results
              return results;
            })
            .catch((exception) => {
              console.log(exception);
            });
        });
      }, Promise.resolve([])).then((results) => {
        console.log(`PATHS PRERENDERED: ${results.length}`);
        return nightmare.end();
      });

    })
    .catch((error) => {

    });