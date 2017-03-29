<?php
namespace application\algorithm;

use application\extend\BaseComponent;

class cacheAlgorithm extends BaseComponent
{
    private $_duration;
    private $_starttime;
    private $_endtime;
    
    function __construct($duration, $starttime, $endtime)
    {
        $this->_duration = $duration;
        $this->_starttime = date('Y-m-d H:i:s', strtotime($starttime));
        $this->_endtime = date('Y-m-d H:i:s', strtotime($endtime));
    }
    
    function formatTime($time)
    {
        return date('Y-m-d H:i:00', strtotime($time));
    }
    
    function cds_online($sn = array())
    {
        $sn = $this->combineSns($sn);
        $cds_detail = array();
        switch ($this->_duration) {
            case 30*60:
                $time = 'if( date_format(ctime,"%i")<30,date_format(ctime,"%Y-%m-%d %H:00:00"),date_format(ctime,"%Y-%m-%d %H:30:00") )';
                break;
            case 60*60:
                $time = 'date_format(ctime,"%Y-%m-%d %H:00:00")';
                break;
            case 2*60*60:
                $time = 'concat(date_format(ctime,"%Y-%m-%d")," ",floor(date_format(ctime,"%H")/2)*2,":00:00")';
                break;
            case 24*60*60:
                $time = 'date_format(ctime,"%Y-%m-%d 00:00:00")';
                break;
            default:
                $time = '';
        }
        if (!empty($time)) {
            $result = $this->model('feedbackHistory')
            ->where('ctime >= ? and ctime < ?', array(
                $this->_starttime,$this->_endtime
            ))
            ->group('time')
            ->order('time', 'asc')
            ->in('sn', $sn)
            ->select(array(
                'time' => $time,
                'count' => 'count(distinct(sn))'
            ));
            foreach ($result as $r) {
                $cds_detail[$r['time']] = $r['count'];
            }
            $cds_max = empty($cds_detail)?0:max($cds_detail);
        }
        return $cds_detail;
    }
    
    function traffic_stat($sn = array())
    {
        $sn = $this->combineSns($sn);
        $traffic_stat = array();
        $traffic_stat_sn = array();
        $result = $this->model('traffic_stat')
        ->where('create_time>=? and create_time<?', array(
            $this->_starttime,$this->_endtime
        ))
        ->select(array(
            'sn',
            'create_time',
            'service',
            'cache',
            'monitor',
            'online_user',
            'hit_user',
        ));
        foreach ($result as $r) {
            if (in_array($r['sn'], $sn, true)) {
                $time = $this->formatTime($r['create_time']);
                $short_sn = substr($r['sn'], 3);
                if (!isset($traffic_stat[$time])) {
                    $traffic_stat[$time] = array(
                        'service' => 0,
                        'cache' => 0,
                        'monitor' => 0,
                        'icache_cache' => 0,
                        'vpe_cache' => 0,
                        'online' => 0,
                        'hit' => 0,
                    );
                }
                if (!isset($traffic_stat_sn[$short_sn][$time])) {
                    $traffic_stat_sn[$short_sn][$time] = array(
                        'service' => 0,
                        'cache' => 0,
                        'monitor' => 0,
                        'icache_cache' => 0,
                        'vpe_cache' => 0,
                        'online' => 0,
                        'hit' => 0,
                    );
                }
                $traffic_stat[$time]['service'] += $r['service']*1024;
                $traffic_stat[$time]['cache'] += $r['cache']*1024;
                $traffic_stat[$time]['monitor'] += $r['monitor']*1024;
                $traffic_stat[$time]['icache_cache'] += $r['cache']*1024;
                $traffic_stat[$time]['online'] += $r['online_user'];
                $traffic_stat[$time]['hit'] += $r['hit_user'];
                
                $traffic_stat_sn[$short_sn][$time]['service'] += $r['service']*1024;
                $traffic_stat_sn[$short_sn][$time]['cache'] += $r['cache']*1024;
                $traffic_stat_sn[$short_sn][$time]['monitor'] += $r['monitor']*1024;
                $traffic_stat_sn[$short_sn][$time]['icache_cache'] += $r['cache']*1024;
                $traffic_stat_sn[$short_sn][$time]['online'] += $r['online_user'];
                $traffic_stat_sn[$short_sn][$time]['hit'] += $r['hit_user'];
            }
        }
        
        $result = $this->model('xvirt_traffic_stat')
        ->where('make_time>=? and make_time<?', array(
            $this->_starttime,$this->_endtime
        ))
        ->select(array(
            'make_time',
            'sn',
            'service',
            'cache',
        ));
        foreach ($result as $r) {
            if (in_array($r['sn'], $sn, true)) {
                $short_sn = substr($r['sn'], 3);
                $time = $this->formatTime($r['make_time']);
                if (!isset($traffic_stat[$time])) {
                    $traffic_stat[$time] = array(
                        'service' => 0,
                        'cache' => 0,
                        'monitor' => 0,
                        'icache_cache' => 0,
                        'vpe_cache' => 0,
                        'online' => 0,
                        'hit' => 0,
                    );
                }
                if (!isset($traffic_stat_sn[$short_sn][$time])) {
                    $traffic_stat_sn[$short_sn][$time] = array(
                        'service' => 0,
                        'cache' => 0,
                        'monitor' => 0,
                        'icache_cache' => 0,
                        'vpe_cache' => 0,
                        'online' => 0,
                        'hit' => 0,
                    );
                }
                $traffic_stat[$time]['service'] -= $r['service'];
                $traffic_stat[$time]['cache'] -= $r['cache'];
                $traffic_stat[$time]['icache_cache'] -= $r['cache'];
                $traffic_stat_sn[$short_sn][$time]['service'] -= $r['service'];
                $traffic_stat_sn[$short_sn][$time]['cache'] -= $r['cache'];
                $traffic_stat_sn[$short_sn][$time]['icache_cache'] -= $r['cache'];
            }
        }
        
        $sn = array_map(function ($s) {
            return substr($s, 3);
        }, $sn);
        $result = $this->model('cdn_traffic_stat')
        ->where('make_time >= ? and make_time<?', array(
            $this->_starttime,$this->_endtime
        ))
        ->select(array(
            'sn',
            'make_time',
            'service',
            'cache',
            'monitor',
        ));
        foreach ($result as $r) {
            $short_sn = substr($r['sn'], 3);
            if (in_array($short_sn, $sn)) {
                $time = $this->formatTime($r['make_time']);
                if (!isset($traffic_stat[$time])) {
                    $traffic_stat[$time] = array(
                        'service' => 0,
                        'cache' => 0,
                        'monitor' => 0,
                        'icache_cache' => 0,
                        'vpe_cache' => 0,
                        'online' => 0,
                        'hit' => 0,
                    );
                }
                if (!isset($traffic_stat_sn[$short_sn][$time])) {
                    $traffic_stat_sn[$short_sn][$time] = array(
                        'service' => 0,
                        'cache' => 0,
                        'monitor' => 0,
                        'icache_cache' => 0,
                        'vpe_cache' => 0,
                        'online' => 0,
                        'hit' => 0,
                    );
                }
                $traffic_stat[$time]['service'] += $r['service'];
                $traffic_stat[$time]['cache'] += $r['cache'];
                $traffic_stat[$time]['monitor'] += $r['monitor'];
                $traffic_stat[$time]['vpe_cache'] += $r['cache'];
                
                $traffic_stat_sn[$short_sn][$time]['service'] += $r['service'];
                $traffic_stat_sn[$short_sn][$time]['cache'] += $r['cache'];
                $traffic_stat_sn[$short_sn][$time]['monitor'] += $r['monitor'];
                $traffic_stat_sn[$short_sn][$time]['vpe_cache'] += $r['cache'];
            }
        }
        
        $temp_traffic_stat_sn = array();
        $temp_traffic_stat = array();
        $max_traffic_stat = array(
            'service' => 0,
            'cache' => 0,
            'monitor' => 0,
            'max_cache' => 0,
            'icache_cache' => 0,
            'vpe_cache' => 0,
            'online' => 0,
            'hit' => 0,
        );
        $max_traffic_stat_sn = array();
        $max_traffic_stat_cache = 0;
        $max_traffic_stat_sn_cache = array();
        $max_traffic_stat_online = 0;
        $max_traffic_stat_sn_online = array();
        $max_traffic_stat_hit = 0;
        $max_traffic_stat_sn_hit = array();
        $t = '';
        $i = 0;
        for ($t_time = $this->_starttime; strtotime($t_time) < strtotime($this->_endtime); $t_time = date('Y-m-d H:i:s', strtotime($t_time)+60)) {
            $i += 60;
            if (empty($t)) {
                $t = $t_time;
            }
            if (isset($traffic_stat[$t_time])) {
                if ($traffic_stat[$t_time]['service'] > $max_traffic_stat['service']) {
                    $max_traffic_stat = $traffic_stat[$t_time];
                }
                if ($traffic_stat[$t_time]['cache'] > $max_traffic_stat_cache) {
                    $max_traffic_stat_cache = $traffic_stat[$t_time]['cache'];
                }
                if ($traffic_stat[$t_time]['online'] > $max_traffic_stat_online) {
                    $max_traffic_stat_online = $traffic_stat[$t_time]['online'];
                }
                if ($traffic_stat[$t_time]['hit'] > $max_traffic_stat_hit) {
                    $max_traffic_stat_hit = $traffic_stat[$t_time]['hit'];
                }
            }
            
            foreach ($traffic_stat_sn as $sn => $v) {
                if (!isset($max_traffic_stat_sn_cache[$sn])) {
                    $max_traffic_stat_sn_cache[$sn] = 0;
                }
                if (!isset($max_traffic_stat_sn_online[$sn])) {
                    $max_traffic_stat_sn_online[$sn] = 0;
                }
                if (!isset($max_traffic_stat_sn_hit[$sn])) {
                    $max_traffic_stat_sn_hit[$sn] = 0;
                }
                if (!isset($max_traffic_stat_sn[$sn])) {
                    $max_traffic_stat_sn[$sn] = array(
                        'service' => 0,
                        'cache' => 0,
                        'monitor' => 0,
                        'max_cache' => 0,
                        'icache_cache' => 0,
                        'vpe_cache' => 0,
                        'online' => 0,
                        'hit' => 0,
                    );
                }
                if (isset($v[$t_time])) {
                    if ($v[$t_time]['service'] > $max_traffic_stat_sn[$sn]['service']) {
                        $max_traffic_stat_sn[$sn] = $v[$t_time];
                    }
                    if ($v[$t_time]['cache'] > $max_traffic_stat_sn_cache[$sn]) {
                        $max_traffic_stat_sn_cache[$sn] = $v[$t_time]['cache'];
                    }
                    if ($v[$t_time]['online'] > $max_traffic_stat_sn_online[$sn]) {
                        $max_traffic_stat_sn_online[$sn] = $v[$t_time]['online'];
                    }
                    if ($v[$t_time]['hit'] > $max_traffic_stat_sn_hit[$sn]) {
                        $max_traffic_stat_sn_hit[$sn] = $v[$t_time]['hit'];
                    }
                }
            }
            //unset($v);
            if ($i == $this->_duration) {
                $max_traffic_stat['max_cache'] = $max_traffic_stat_cache;
                $max_traffic_stat['online'] = $max_traffic_stat_online;
                $max_traffic_stat['hit'] = $max_traffic_stat_hit;
                $temp_traffic_stat[$t] = $max_traffic_stat;
                
                foreach ($max_traffic_stat_sn as $sn => $v) {
                    $temp_traffic_stat_sn[$t][$sn] = $v;
                    $temp_traffic_stat_sn[$t][$sn]['max_cache'] = $max_traffic_stat_sn_cache[$sn];
                    $temp_traffic_stat_sn[$t][$sn]['online'] = $max_traffic_stat_sn_online[$sn];
                    $temp_traffic_stat_sn[$t][$sn]['hit'] = $max_traffic_stat_sn_hit[$sn];
                }
                
                $t = '';
                $i = 0;
                $max_traffic_stat_cache = 0;
                $max_traffic_stat_hit = 0;
                $max_traffic_stat_online = 0;
                $max_traffic_stat = array(
                    'service' => 0,
                    'cache' => 0,
                    'monitor' => 0,
                    'max_cache' => 0,
                    'icache_cache' => 0,
                    'vpe_cache' => 0,
                    'online' => 0,
                    'hit' => 0,
                );
                $max_traffic_stat_sn = array();
                $max_traffic_stat_sn_cache = array();
                $max_traffic_stat_sn_hit = array();
                $max_traffic_stat_sn_online = array();
            }
        }
        
        return array(
            'traffic_stat' => $temp_traffic_stat,
            'traffic_stat_sn' => $temp_traffic_stat_sn,
        );
    }
}
