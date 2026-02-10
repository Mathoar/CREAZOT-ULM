# #syntax=docker/dockerfile:1.4


# # Versions
# # FROM node:21-alpine AS node_upstream
# FROM node@sha256:09e2b3d9726018aecf269bd35325f46bf75046a643a66d28360ec71132750ec8 AS node_upstream


# # Base stage for dev and build
# FROM node_upstream AS base

# # Check https://github.com/nodejs/docker-node/tree/b4117f9333da4138b03a546ec926ef50a31506c3#nodealpine to understand why libc6-compat might be needed.
# # hadolint ignore=DL3018
# RUN apk add --no-cache libc6-compat

# WORKDIR /srv/app

# RUN corepack enable && \
#     corepack prepare pnpm@10.28.2 --activate && \
#     pnpm config -g set store-dir /.pnpm-store

# # Next.js collects completely anonymous telemetry data about general usage.
# # Learn more here: https://nextjs.org/telemetry
# # Delete the following line in case you want to enable telemetry during dev and build.
# ENV NEXT_TELEMETRY_DISABLED 1


# # Development image
# FROM base as dev

# EXPOSE 3000
# ENV PORT 3000
# ENV HOSTNAME localhost

# CMD ["sh", "-c", "pnpm install; pnpm dev"]


# FROM base AS builder

# COPY --link pnpm-lock.yaml ./
# RUN pnpm fetch --prod

# COPY --link . .

# ARG AUTH_SECRET
# # https://nextjs.org/docs/app/building-your-application/configuring/environment-variables#bundling-environment-variables-for-the-browser
# ARG NEXT_PUBLIC_OIDC_SERVER_URL

# RUN	pnpm install --frozen-lockfile --offline --prod && \
# 	pnpm run build


# # Production image, copy all the files and run next
# FROM node_upstream AS prod

# WORKDIR /srv/app

# ENV NODE_ENV production
# # Delete the following line in case you want to enable telemetry during runtime.
# ENV NEXT_TELEMETRY_DISABLED 1

# RUN addgroup --system --gid 1001 nodejs; \
# 	adduser --system --uid 1001 nextjs

# COPY --from=builder --link /srv/app/public ./public

# # Set the correct permission for prerender cache
# RUN mkdir .next; \
# 	chown nextjs:nodejs .next

# # Automatically leverage output traces to reduce image size
# # https://nextjs.org/docs/advanced-features/output-file-tracing
# COPY --from=builder --link --chown=1001:1001 /srv/app/.next/standalone ./
# COPY --from=builder --link --chown=1001:1001 /srv/app/.next/static ./.next/static

# USER nextjs

# EXPOSE 3000

# ENV PORT 3000
# ENV HOSTNAME "0.0.0.0"

# CMD ["node", "server.js"]



#syntax=docker/dockerfile:1.4

# Versions
FROM node@sha256:09e2b3d9726018aecf269bd35325f46bf75046a643a66d28360ec71132750ec8 AS node_upstream

# Base stage for dev and build
FROM node_upstream AS base

RUN apk add --no-cache libc6-compat

WORKDIR /srv/app

RUN corepack enable && \
    corepack prepare pnpm@10.28.2 --activate && \
    pnpm config -g set store-dir /.pnpm-store

ENV NEXT_TELEMETRY_DISABLED 1

#######################
# Development image
#######################
FROM base as dev

EXPOSE 3000
ENV PORT 3000
ENV HOSTNAME localhost

CMD ["sh", "-c", "pnpm install; pnpm dev"]

#######################
# Build image
#######################
FROM base AS builder

# Copier le lockfile pour le cache
COPY --link pnpm-lock.yaml ./
RUN pnpm fetch --prod

# Copier tout le code source
COPY --link . .

# Définir les arguments pour Next.js
ARG AUTH_SECRET
ARG NEXT_PUBLIC_OIDC_SERVER_URL
ARG NEXT_PUBLIC_API_BASE_URL

# Les arguments deviennent des variables d'environnement visibles au build
ENV AUTH_SECRET=$AUTH_SECRET
ENV NEXT_PUBLIC_OIDC_SERVER_URL=$NEXT_PUBLIC_OIDC_SERVER_URL
ENV NEXT_PUBLIC_API_BASE_URL=$NEXT_PUBLIC_API_BASE_URL

# Installer les dépendances et builder l'application
RUN pnpm install --frozen-lockfile --prod && \
    pnpm run build

#######################
# Production image
#######################
FROM node_upstream AS prod

WORKDIR /srv/app
ENV NODE_ENV production
ENV NEXT_TELEMETRY_DISABLED 1

RUN addgroup --system --gid 1001 nodejs; \
    adduser --system --uid 1001 nextjs

# Copier public et build Next.js
COPY --from=builder --link /srv/app/public ./public

# Préparer le dossier .next et donner les droits
RUN mkdir .next; \
    chown nextjs:nodejs .next

# Copier l'application standalone et les fichiers statiques
COPY --from=builder --link --chown=1001:1001 /srv/app/.next/standalone ./
COPY --from=builder --link --chown=1001:1001 /srv/app/.next/static ./.next/static

USER nextjs

EXPOSE 3000
ENV PORT 3000
ENV HOSTNAME "0.0.0.0"

CMD ["node", "server.js"]

