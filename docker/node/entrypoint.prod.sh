#!/bin/sh

set -e # エラーが起きたら即座にスクリプトを停止する

cd /app

# 依存パッケージが無ければインストール(package-lock.jsonに忠実な npm ci を使う)
if [ ! -d node_modules ]; then
  echo "Installing npm packages..."
  npm ci
fi

# 本番ビルドがなければビルド(.outputに成果物が出る)
if [ ! -d .output ]; then
  echo "Building Nuxt for production..."
  npm run build
fi

# Nuxtの本番SSRサーバーを起動(ポート3000・HOST/PORTはcomposeのenvironmentで指定)
exec node .output/server/index.mjs
