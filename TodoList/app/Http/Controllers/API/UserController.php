<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\User;
use App\Http\Controllers\Controller;

class UserController extends Controller
{    
    
    /**
     * login
     *
     * @param Request $request
     * @return \lluminate\Http\Response
     */
    public function login(Request $request) {
        $inputVal = $request->all();
        $validator = $this->validateVal($inputVal, 'login');
            
        if ($validator['status']) {
            $client = \DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();            

            $data = [
                'grant_type' => 'password',
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'username' => request('email'),
                'password' => request('password'),
            ]; 

            $request = Request::create('/oauth/token', 'POST', $data);            

            return app()->handle($request);
        }else {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => [
                        $validator['errors']
                    ],
                ]
            ],400,[],JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $inputVal = $request->all();

        $validator = $this->validateVal($inputVal,'store');

        $inputVal['password'] = bcrypt($inputVal['password']);        

        if ($validator['status']) {
            $id = User::create($inputVal)->id;
            return response()->json([
                'data' => [
                    'code' => 200,
                    'message' => [
                        'success',
                        $id                    
                    ],
                ]
            ],200,[],JSON_UNESCAPED_UNICODE);
        }else {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => [
                        $validator['errors']
                    ],
                ]
            ],400,[],JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(!is_numeric($id)) {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => [
                        'invalid id number'
                    ],
                ]
            ],400,[],JSON_UNESCAPED_UNICODE);
        }

        $result = User::find($id);
        if ($result) {
            return response()->json([
                'data' => [
                    'code' => 200,
                    'message' => $result,
                ]
            ],200,[],JSON_UNESCAPED_UNICODE);
        }else {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => [
                        'data does not exist'
                    ],
                ]
            ],400,[],JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $inputVal = $request->all();

        $validator = $this->validateVal($inputVal,'update');

        if ($validator['status']) {
            $result = User::find($id);

            if ($result) {
                $result->update($inputVal);
            }else {
                return response()->json([
                    'error' => [
                        'code' => 400,
                        'message' => [
                            'invalid id number'
                        ],
                    ]
                ],400,[],JSON_UNESCAPED_UNICODE);
            }            

            return response()->json([
                'data' => [
                    'code' => 200,
                    'message' => [
                        'success',
                        $id                    
                    ],
                ]
            ],200,[],JSON_UNESCAPED_UNICODE);
        }else {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => [
                        $validator['errors']
                    ],
                ]
            ],400,[],JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!is_numeric($id)) {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => [
                        'invalid id number'
                    ],
                ]
            ],400,[],JSON_UNESCAPED_UNICODE);
        }

        $result = User::find($id);
        if ($result) {
            $result->delete();
            return response()->json([
                'data' => [
                    'code' => 200,
                    'message' => 'success',
                ]
            ],200,[],JSON_UNESCAPED_UNICODE);
        }else {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => [
                        'data does not exist'
                    ],
                ]
            ],400,[],JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * validateVal
     *
     * @param mixed $data
     * @param mixed $type
     * @return array status|errors
     */
    private function validateVal($data, $type) {        
        $errors = [];

        switch ($type) {
            case 'store':
                $rules = [                    
                    'name' => ['required','regex:/[a-zA-Z가-힣]/','max:10'],
                    'email' => ['required','email','unique:users,email'],
                    'password' => ['required','confirmed'],            
                    'password_confirmation' => ['required'],
                ];
                break;
            case 'update':
                $rules = [
                    'name' => ['regex:/[a-zA-Z가-힣]/','max:10'],
                    'email' => ['email','unique:users,email'],
                    'password' => ['confirmed'],                                
                ];
                break;
            case 'login':
                $rules = [                    
                    'email' => ['required','email'],
                    'password' => ['required'],            
                ];
                break;
        }

        $messages = [
            'name.required' => '이름을 입력해주세요.',
            'name.regex' => '이름은 영문, 한글만 가능합니다.',
            'name.max' => '이름의 최대 길이는 10글자입니다.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '이메일이 형식에 맞지 않습니다.',
            'email.unique' => '이미 가입된 이메일입니다.',
            'password.required' => '비밀번호를 입력해주세요',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
            'password_confirmation.required' => '비밀번호 확인란을 입력해주세요.',
        ];

        $validator = \Validator::make($data, $rules, $messages);
        
        if ($validator->fails()) {
            $status = false;            
            for ($i = 0; $i < count($validator->errors()->keys()); $i++) {
                $errors[$validator->errors()->keys()[$i]] = $validator->errors()->get($validator->errors()->keys()[$i])[0];
            }
        } else {
            $status = true;            
        }

        return ['status' => $status, 'errors' => $errors];
    }
}
