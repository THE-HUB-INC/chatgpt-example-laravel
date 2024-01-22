<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatGptController extends Controller
{
    private $openaiClient;

    public function __construct()
    {
        $api_key = env('CHAT_GPT_KEY');
        $this->openaiClient = \Tectalic\OpenAi\Manager::build(
            new \GuzzleHttp\Client(),
            new \Tectalic\OpenAi\Authentication($api_key)
        );
    }

    /**
     * index
     *
     * @param  Request  $request
     */
    public function index(Request $request)
    {
        return view('chat');
    }

    /**
     * ChatGPT API呼び出し
     * ライブラリ
     */
    function chat_gpt($system, $user)
    {
        // パラメータ
        $data = array(
            "model" => "gpt-4-vision-preview",
            "messages" => [
                [
                    "role" => "system",
                    "content" => $system
                ],
                [
                    "role" => "user",
                    "content" => $user
                ]
            ]
        );

        try {
            $response = $this->openaiClient->chatCompletions()->create(
                new \Tectalic\OpenAi\Models\ChatCompletions\CreateRequest($data)
            )->toModel();

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            return "ERROR";
        }
    }

    function whisper_speech_to_text($audioFilePath)
    {
        try {
            // Whisperモデルを使用して音声認識
            $response = $this->openaiClient->audioTranscriptions()->create(
                new \Tectalic\OpenAi\Models\AudioTranscriptions\CreateRequest([
                    'file' => $audioFilePath,
                    'model' => 'whisper-1',
                ])
            )->toModel();

            // 音声認識の結果を返す
            return $response->text;
        } catch (\Exception $e) {
            // エラー処理
            return "ERROR: " . $e->getMessage();
        }
    }


    /**
     * chat
     *
     * @param  Request  $request
     */
    public function chat(Request $request)
    {
        // バリデーション
        $request->validate([
            'sentence' => 'required',
            'audioFile' => 'file|mimes:wav,mp3',
        ]);

        // 音声ファイルを取得
        if($request->hasFile('audioFile'))
        {
            $audioFile = $request->file('audioFile');
            // publicディレクトリ内の'uploads'フォルダに保存
            $destinationPath = 'uploads';
            $audioFile->move($destinationPath, $audioFile->getClientOriginalName());
            $audioFilePath = '/var/www/html/public/uploads/' . $audioFile->getClientOriginalName();
            $sentence = $this->whisper_speech_to_text($audioFilePath);
        } else {
            $sentence = $request->sentence;
        }

        // ChatGPT API処理
        $chat_response = $this->chat_gpt("テキストを英語にしてください", $sentence);

        return view('chat', compact('sentence', 'chat_response'));
    }
}
