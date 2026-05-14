#!/bin/sh

set -e # エラーが起きたら即座にスクリプトを停止する

cd /app

# node_modulesが存在しない場合のみnpm installを実行
# (すでにある場合はスキップして起動を速くする)
if [ ! -d node_modules ] || [ ! -f node_modules/.package-lock.json ]; then
  echo "Installing npm packages..."
  npm install
fi

# Nuxt.jsの開発サーバーを起動
exec npm run dev
