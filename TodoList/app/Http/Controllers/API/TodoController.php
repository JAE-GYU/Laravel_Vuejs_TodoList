<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Todo;
use App\Http\Controllers\Controller;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {        
        $reqestQuery = request()->query();
        array_key_exists('page', $reqestQuery) ? $page = $reqestQuery['page'] : $page = 0;
        array_key_exists('perPage', $reqestQuery) ? $perPage = $reqestQuery['perPage'] : $perPage =15;
        
        if (!is_numeric($page) || !is_numeric($perPage)) {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => [
                        'invalid query string'
                    ],
                ]
            ],400,[],JSON_UNESCAPED_UNICODE);
        }

        $totalNum = Todo::count();
        $totalPage = ceil($totalNum/$perPage);                

        if ($totalPage < $page) {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => [
                        'page does not exist'
                    ],
                ]
            ],400,[],JSON_UNESCAPED_UNICODE);    
        }

        $result = Todo::where('id','>',(($page -1 ) * $perPage))->limit($perPage)->get();
        
        return response()->json([
            'data' => [
                'code' => 200,
                'message' => $result,
            ]
        ],200,[],JSON_UNESCAPED_UNICODE); 
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

        if ($validator['status']) {
            $id = Todo::create($request->all())->id;
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

        $result = Todo::find($id);
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
            $result = Todo::find($id);

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

        $result = Todo::find($id);
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
                    'task' => ['required'],                    
                    'category' => ['required']       
                ];
                break;
            case 'update':
                $rules = [];
                break;
        }

        $messages = [            
            'task.required' => '할 일을 입력해주세요.',            
            'category.required' => '카테고리를 입력해주세요.',
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
