<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite('resources/css/app.css')

</head>
<body>

  {{-- フォーム --}}
  {{-- ロゴを貼る --}}
  <img src="{{ asset('icons8.png') }}" alt="ChatGPT_logo" width="300" height="300">
  <form method="POST" action="{{ route('vision_api') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" name="image" accept="image/png, image/jpeg">
    <button type="submit" class="rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Button text</button>
  </form>
  @if ($errors->any())
  <ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
  @endif

  {{-- 結果 --}}
  {{-- {{ isset($chat_response) ? $chat_response : '' }} --}}
</body>
</html>
