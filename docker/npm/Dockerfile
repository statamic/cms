FROM node:14-alpine

RUN mkdir -m 777 /root/.npm
VOLUME /root/.npm

RUN mkdir /app
VOLUME /app
WORKDIR /app
ENTRYPOINT [ "npm" ]
