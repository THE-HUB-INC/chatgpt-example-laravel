## セッティング
git clone 

cd chatgpt-example-laravel

cp .env{.example,}

composer install

sail up -d

sail npm install

php artisan key:generate

sail npm run dev

## APIの設定

Get your API key(https://platform.openai.com/account/api-keys)

APIkeyを.envに設定　
```OPENAI_API_KEY=ここにキーを記述```

無料枠だと429エラーとなります。 そのため課金する必要があります。 有料には最低でも$5かかります。 Billing settings（https://platform.openai.com/account/billing/overview）
