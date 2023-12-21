<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\CommonMark\CommonMarkConverter;

class WhisperController extends Controller
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

    public function whisper()
    {
        /** @var \Tectalic\OpenAi\Models\AudioTranscriptions\CreateResponse $response */
        $response = $this->openaiClient->audioTranscriptions()->create(
            new \Tectalic\OpenAi\Models\AudioTranscriptions\CreateRequest([
                'file' => public_path('sample.m4a'),
                'model' => 'whisper-1',
            ])
        )->toModel();

        $chat_response = $this->chat_gpt("私はIT人材の転職エージェントで働いています。エンジニア求職者との面談の内容をお渡ししますので、以下に示すフォーマットに沿って要約してください。いただいたアウトプットをデータベース化して保存したいと思っています。フォーマットは、「エンジニアとしての勤務経歴」「その他の職種の経歴」「リモート勤務を希望するか否か・出社について」「今まで経験した技術」「今後触りたい技術」「どのような会社に転職したいか」「その他」で、markdown形式で、重要箇所は太字にするなど、装飾を多用してください。", $response->text);

        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $chat_response = $converter->convert($chat_response);

        return view('whisper', compact('chat_response'));
    }

    /**
     * ChatGPT API呼び出し
     * ライブラリ
     */
    function chat_gpt($system, $user)
    {
        // APIキー
        $api_key = env('CHAT_GPT_KEY');

        // パラメータ
        $data = array(
            "model" => "gpt-3.5-turbo",
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
}
