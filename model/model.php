<?php 
class Log_model extends CI_Model {
    
    public function add_log($data, $logTable, $logAction, $ID = 0)
    {
        // ดึงข้อมูล session ของผู้ใช้
        $ss_user = $this->session->userdata('logged_in');

        // สร้างข้อมูล log ที่จะบันทึก
        $log = [
            'logContent' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'logTable' => $logTable,
            'logAction' => $logAction,
            'ID' => $ID,
            'logIP' => $this->getIP(),
            'authID' => $ss_user->authID
        ];

        // บันทึกข้อมูล log ลงฐานข้อมูล
        $this->db->insert('log', $log);
    }

    public function getIP()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $ip;
    }
}
?>
