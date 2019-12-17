<?php declare(strict_types=1);

namespace App\Graph;

use App\Graph\Encryption\Encrypt_v3;

trait HasResourceActions
{
    /**
     * 列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $string = '加密字符串';
        $input = request()->json()->all();

        $string = '';
        foreach ($input as $k=>$v){
            $string .= $k.$v;
        }

        $encode = Encrypt_v3::encrypt($string);
       // var_dump($encode) . PHP_EOL;       // Gr0DHeHrRw7KGBLcSOzj
        $decode = Encrypt_v3::decrypt($encode);

        var_dump($decode) . PHP_EOL;     // 加密字符串
        exit;



        $grid =  new Grid();

        return $grid->build();
    }

    /**
     * 新增
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws Exceptions\FormException
     */
    public function store()
    {
        $form = new Form();

        return $form->store();
    }

    /**
     * 更新
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws Exceptions\FormException
     * @throws Exceptions\GridException
     */
    public function update($id)
    {
        $form = new Form();

        return $form->update($id);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $form = new Form();

        return $form->destroy($id);
    }

    public function show($id)
    {
        $show = new Show();

        return $show->detail($id);
    }

}
