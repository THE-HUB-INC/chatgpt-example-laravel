<html>

<head>
    <meta charset='utf-8' />
</head>

<body>
    {{-- フォーム --}}
    {{-- ロゴを貼る --}}
    <img src="{{ asset('icons8.png') }}" alt="ChatGPT_logo" width="300" height="300">
    <form method="POST" enctype="multipart/form-data">
        @csrf
        {{-- <textarea rows="10" cols="50" name="sentence">{{ isset($sentence) ? $sentence : '' }}</textarea> --}}
        {{-- 画像ファイルを入力してください --}}
        <input type="file" name="image" accept="image/png, image/jpeg">
        <button type="submit">ChatGPT</button>
    </form>
    @if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

    {{-- 結果 --}}
    {{ isset($chat_response) ? $chat_response : '' }}
</body>

</html>
