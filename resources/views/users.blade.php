<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite('resources/css/app.css')

</head>
<body>
  <div class="bg-white py-24 sm:py-32">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
      <div class="mx-auto max-w-2xl sm:text-center">
        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">タレントマネジメントシステム</h2>
        <p class="mt-6 text-lg leading-8 text-gray-600"><code>`/chat`</code>ページでアップロードされた職務経歴書・履歴書を元に生成されたタレント一覧です。</p>
        <div class="text-left">
          <h3>## 課題メモ</h3>
          <ul>
            <li>・職務履歴書にある画像をそのまま切り取って保存したい（採用において画像はセンシティブなので微妙？）</li>
          </ul>
        </div>
      </div>
      <ul role="list" class="mx-auto mt-20 grid max-w-2xl grid-cols-1 gap-x-6 gap-y-20 sm:grid-cols-2 lg:max-w-4xl lg:gap-x-8 xl:max-w-none">
        @foreach($users as $user)
          <li class="flex flex-col gap-6 xl:flex-row">
            <img class="aspect-[4/5] w-52 flex-none rounded-2xl object-cover" src="{{ $user->image }}" alt="">
            <div class="flex-auto">
              <h3 class="text-lg font-semibold leading-8 tracking-tight text-gray-900">{{ $user->name }}</h3>
              <p class="text-base leading-7 text-gray-600">エンジニア</p>
              <p class="mt-6 text-base leading-7 text-gray-600">
                {{ $user->introduction }}
              </p>
              <div class="mt-2 space-y-1">
                @foreach($user->skills as $skill)
                  <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">{{ $skill->name }}</span>
                @endforeach
              </div>
            </div>
          </li>
        @endforeach
      </ul>
    </div>
  </div>
</body>
</html>
