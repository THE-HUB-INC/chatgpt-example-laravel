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

    /**
     * ChatGPT API呼び出し
     * ライブラリ
     */
    function chat_gpt_vision($system, $image)
    {
        // パラメータ
        $data = array(
            "model" => "gpt-4-vision-preview",
            "messages" => [
                [
                    "role" => "user",
                    'content' => [
                        ['type' => 'text', 'text' => $system],
                        ['type' => 'image_url', 'image_url' => ['url' => "data:image/jpeg;base64," . $image]]
                    ]
                ]
            ],
            "max_tokens" => 300,
        );

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . env('CHAT_GPT_KEY'),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $decode = json_decode($response, true);
        $content = $decode['choices'][0]['message']['content'];
        $decode_content = json_decode('"' . $content . '"');

        return $decode_content;
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
        // $request->validate([
        //     'sentence' => 'required',
        //     'audioFile' => 'file|mimes:wav,mp3',
        // ]);

        // 音声ファイルを取得
        // if($request->hasFile('audioFile'))
        // {
        //     $audioFile = $request->file('audioFile');
        //     // publicディレクトリ内の'uploads'フォルダに保存
        //     $destinationPath = 'uploads';
        //     $audioFile->move($destinationPath, $audioFile->getClientOriginalName());
        //     $audioFilePath = '/var/www/html/public/uploads/' . $audioFile->getClientOriginalName();
        //     $sentence = $this->whisper_speech_to_text($audioFilePath);
        // } else {
        //     $sentence = $request->sentence;
        // }

        // encodeしてrequestから画像を取
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $destinationPath = 'uploads';
            $image->move($destinationPath, $image->getClientOriginalName());
            $imageFilePath = '/var/www/html/public/uploads/' . $image->getClientOriginalName();
            $content = base64_encode(file_get_contents($imageFilePath));
        } else {
            $content = "";
        } 
        // ChatGPT API処理
        $chat_response = $this->chat_gpt_vision("画像に何が映っているか教えてください。", $content);

        return view('chat', compact('chat_response'));
    }
}
