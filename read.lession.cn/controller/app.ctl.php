<?php


/**
 * app注册登录统计
 * @author admin
 *
 */


class reglog_app_controller extends controller_lib
{


    public function index_action()
    {
        $app_id = input('app_id', '');
        $addTime1 = input('add_time1', '');
        $addTime2 = input('add_time2', '');
        $current_time = date('Y-m-d');
        if (!$addTime1) {
            $addTime1 = $current_time;
        }
        if (!$addTime2) {
            $addTime2 = $current_time;
        }
        $where = [];
        //$addTimeEnd = date('Y-m-d H:i:s',strtotime($addTime2)+86400);
        $where['date'] = ['more', [
            ['>=', $addTime1, 'and'],
            ['<=', $addTime2],
        ]
        ];
        if ($app_id) {
            $where['app_id'] = ['=', $app_id];
        }
        //$newsCheckModel = getInstance('news_check_video_model');
        $page = $this->_getPageData();
        $app_device_model = getInstance('device_app_model');
        $data_0 = $app_device_model->where($where)->limit($page['start'], $page['pageRows'])->order('id desc')->select();
        $app_list = [];
        $where_info = [];
        $app_list_0 = getInstance('app_info_model')->where($where_info)->select();
        foreach ($app_list_0 as $app_inf) {
            //array_push($app_list, [$app_inf['id']=> $app_inf['alias_app_name']]);
            $app_list[$app_inf['appid']] = $app_inf['alias_app_name'];
        }
        //echo var_export($app_list, true);
        $list = [];
        foreach ($data_0 as $info) {
            $list[] = [
                'id' => $info['id'],
                'date' => $info['date'],
                'app_id' => $info['app_id'],
                'app_name' => isset($app_list[$info['app_id']]) ? $app_list[$info['app_id']] : '',
                'new_device_count' => $info['new_device_count'],
                'startup_device_count' => $info['startup_device_count'],
                'startup_count' => $info['startup_count']
            ];
        }
        $tpldata = [
            'states' => $this->getStates(),
            'list' => $list,
            'app_list' => $app_list,
            'app_id' => $app_id,
            'addTime1' => $addTime1,
            'addTime2' => $addTime2,
        ];

        $this->_assign($tpldata);
        $this->_render();
        //echo 'test_ok';
    }


    protected function getStates()
    {
        return [
            '0' => '未审核',
            '1' => '审核通过',
            '2' => '审核不通过',
            '3' => '已发布'
        ];
    }

    protected function getChannels()
    {
        return [
            '1' => '热门',
            '2' => '视频',
            '3' => '足彩',
            '4' => '中国足球',
            '5' => '英超',
            '6' => '西甲',
            '7' => '德甲',
            '8' => '五洲',
            '9' => '意甲'
        ];
    }

    protected function getCityList()
    {
        return [
            '北京' => '北京',
            '上海' => '上海',
            '广州' => '广州',
            '深圳' => '深圳'
        ];
    }

    protected function getTeams()
    {
        return [
            '41' => '利物浦',
            '40' => '曼城',
            '36' => '热刺',
            '47' => '切尔西',
            '51' => '阿森纳 ',
            '39' => '曼联',
        ];
    }


}