# ---- Stage 1: build the Vue app with Node.js ----
FROM node:24.22.1-alpine3.24 AS build

WORKDIR /app

# Dependencies first so this layer is cached until package files change.
COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Vite writes this value into the bundle at build time.
# "/api" = same origin; Nginx below forwards it to the backend container.
ARG VITE_API_BASE_URL=/api
ENV VITE_API_BASE_URL=$VITE_API_BASE_URL

RUN npm run build

# ---- Stage 2: serve the built files with Nginx ----
# Only dist/ is copied, so the final image has no Node.js and no node_modules.
FROM nginx:1.31.8-alpine

LABEL org.opencontainers.image.source="https://github.com/aarmeliaa/uts-pl-24-538562-sv-24509"

COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80
