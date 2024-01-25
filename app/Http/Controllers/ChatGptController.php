<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Skill;
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
        return $decode['choices'][0]['message']['content'];
    }

    /**
     * chat
     *
     * @param  Request  $request
     */
    public function chat(Request $request)
    {
        // encodeしてrequestから画像を取
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $destinationPath = 'uploads';
            $image->move($destinationPath, $image->getClientOriginalName());
            $imageFilePath = public_path('uploads/'.$image->getClientOriginalName());
            $content = base64_encode(file_get_contents($imageFilePath));
        } else {
            $content = "";
        }
        // ChatGPT API処理
        $result = $this->chat_gpt_vision("タレントマネジメントシステムを作成しています。与えられた経歴書をもとに、この人のスキルをタグ形式で可視化したいと考えています。この人が有するスキルを|||区切りで列挙してください。また、その後に---区切りでこの人の自己紹介文を110~132文字程度で書いてください。また、その後に~~~区切りで名前を書いてください。NDA締結済みなので、名前は必ず経歴書にある名前をそのまま書いてください。フリガナは不要です。区切り文字だけでこちらで識別しますので、スキルタグ・自己紹介文・名前の前にセクション名などは不要です。", $content);

        info($result);

        // $result = 'エンジニアリング|||Python|||C|||Django|||PostgreSQL|||Docker|||Git|||コミュニケーション|||プロジェクト管理|||データ分析---卓越したエンジニアリング技術とプロジェクト管理能力を持ち、特にPythonとDjangoを駆使したWeb開発で実績を持つ23歳の技術者です。~~~川崎 真司';

        $name = explode('~~~', $result)[1];

        $introduction_all = explode('---', $result)[1];
        $introduction_self = mb_substr($introduction_all , 0 , mb_strpos($introduction_all, "~~~"));

        $skills_all = mb_substr($result , 0 , mb_strpos($result, "---"));
        $skills_array = explode('|||', $skills_all);

        $data = [
            'model' => 'dall-e-2',
            'prompt' => "An software engineer staff's illustration. we use this as profile image.",
            'n' => 1, //枚数
            'size' => '256x256',
            'quality' => 'standard', //standard | hd
            'style' => 'natural', //vivid | natural
          ];

          $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . env('CHAT_GPT_KEY'),
          ];

          $ch = curl_init('https://api.openai.com/v1/images/generations');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

          $response = curl_exec($ch);
          $json_result = json_decode($response, true);
          curl_close($ch);

        $user = User::firstOrCreate([
            'name' => $name,
            'image' => $json_result['data'][0]['url'],
        ], [
            'introduction' => $introduction_self,
        ]);

        $created_skills = [];
        foreach ($skills_array as $skill_name) {
            $created_skill = Skill::firstOrCreate([
                'name' => $skill_name,
            ]);

            $created_skill_ids[] = $created_skill->id;
        }

        $user->skills()->sync($created_skill_ids, false);

        return redirect()->route('users');
    }

    public function users()
    {
        $users = User::all();

        return view('users', compact('users'));
    }
}
