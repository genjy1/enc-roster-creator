FROM node:22-alpine

WORKDIR /app

# Сначала только манифест — кэшируем слой
COPY package.json package-lock.json ./
RUN npm ci

# Копируем остальное
COPY . .

EXPOSE 5173

# --host — обязательно, иначе Vite не слушает вне контейнера
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
