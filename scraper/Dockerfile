FROM node:22-alpine

WORKDIR /scraper

COPY package.json .
RUN npm install --omit=dev

COPY scrape-hltv.js .

CMD ["node", "scrape-hltv.js"]
