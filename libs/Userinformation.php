<?php
//Userinformation

class Userinformation
{
    //private $data = array();

    public function __construct()
    {

    }

    public function __destruct()
    {

    }
    public function Call_check()
    {
        return true;
    }
    public function check_round($Round, $player)
    {
        $parseData['player'] = $player;
        $out[]               = array();

        switch ($Round) {

            case "1234":
                echo "玩家 1 \n";
                $Round_array = '1234';
                //$key       = "player1" ;
                $key                = "player" . $parseData['player'];
                $out['Round_array'] = $Round_array;
                $out['key']         = $key;
                return $out;
                break;
            case "2341":
                echo "玩家 2 \n";
                $Round_array = '2341';
                //$key       = "player2" ;
                $key                = "player" . $parseData['player'];
                $out['Round_array'] = $Round_array;
                $out['key']         = $key;
                return $out;
                break;
            case "3412":
                echo "玩家 3 \n";
                $Round_array = '3412';
                //$key       = "player3" ;
                $key                = "player" . $parseData['player'];
                $out['Round_array'] = $Round_array;
                $out['key']         = $key;
                return $out;
                break;
            case "4123":
                echo "玩家 4 \n";
                $Round_array = '4123';
                //$key       = "player4" ;
                $key                = "player" . $parseData['player'];
                $out['Round_array'] = $Round_array;
                $out['key']         = $key;
                return $out;
                break;
            default:
                //echo "Your favorite color is neither red, blue, nor green!";
        }

    }

    public function check_hand($payer_user, $P1, $P2, $P3, $P4, $Round)
    {

        //var_dump($payer_user);
        //var_dump($P1);
        //var_dump($P2);
        //var_dump($P4);
        switch ($payer_user) {
            case "1":
                switch ($Round) {
                    case "1234":
                        echo '正確回合';
                        if ($P1 != '14') {
                            $OK = 0;
                            return 0;
                        } else {
                            echo '錯誤手牌';
                            $OK = 1;
                            return 1;
                        }
                        break;
                    default:
                        echo '不正確回合';
                        $OK = 1;
                        return 1;
                }
                break;
            case "2":
                switch ($Round) {
                    case "2341":
                        echo '正確回合';
                        if ($P2 != '14') {
                            $OK = 0;
                            return 0;
                        } else {
                            echo '錯誤手牌';
                            $OK = 1;
                            return 1;
                        }
                        break;
                    default:
                        echo '不正確回合';
                        $OK = 1;
                        return 1;
                }
                break;
            case "3":

                switch ($Round) {
                    case "3412":
                        echo '正確回合';
                        if ($P3 != '14') {
                            $OK = 0;
                            return 0;
                        } else {
                            echo '錯誤手牌';
                            $OK = 1;
                            return 1;
                        }
                        break;
                    default:
                        echo '不正確回合';
                        $OK = 1;
                        return 1;
                }
                break;
            case "4":
                switch ($Round) {
                    case "4123":
                        echo '正確回合';
                        if ($P4 != '14') {
                            $OK = 0;
                            return 0;
                        } else {
                            echo '錯誤手牌';
                            $OK = 1;
                            return 1;
                        }
                        break;
                    default:
                        echo '不正確回合';
                        $OK = 1;
                        return 1;
                }
                break;
            default:
                return 0;
                //echo "Your favorite color is neither red, blue, nor green!";
        }
    }
    public function cardData_player($cardData)
    {

        $cardData_player[0] = count($cardData["player1"]);
        $cardData_player[1] = count($cardData["player2"]);
        $cardData_player[2] = count($cardData["player3"]);
        $cardData_player[3] = count($cardData["player4"]);
        return $cardData_player;
    }

    public function check_outCard($payer_user, $Round, $P1, $P2, $P3, $P4)
    {
        //echo $payer_user."\n";
        //echo $Round."\n";
        //echo $P1."\n";
        //echo $P2."\n";
        //echo $P3."\n";
        //echo $P4."\n";
        //

        switch ($payer_user) {
            case "1":
                switch ($Round) {
                    case "1234":
                        echo '正確回合';
                        if ($P1 == '13' and $P2 == '13' and $P3 == '13' and $P4 == '13') {
                            echo '尚未開局';
                            $OK = 1;
                            return 1;

                        } else {
                            //echo $P1 . " - " . $P2 . " - " . $P3 . " - " . $P4 . " - ";
                            if ($P1 == '14' or $P1 == '13' and $P2 == '13' and $P3 == '13' and $P4 == '13') {
                                $OK = 0;
                                return 0;
                            } else {
                                echo '錯誤手牌';
                                $OK = 1;
                                return 1;
                            }
                            //echo $OK;
                            break;
                        }

                    default:
                        echo '不正確回合';
                        $OK = 1;
                        return 1;
                }
                break;
            case "2":
                switch ($Round) {
                    case "2341":
                        echo '正確回合';

                        if ($P2 == '14' or $P2 == '13' and $P1 == '13' and $P3 == '13' and $P4 == '13') {
                            $OK = 0;
                            return 0;
                        } else {
                            echo '錯誤手牌';
                            $OK = 1;
                            return 1;
                        }
                        break;
                    default:
                        echo '不正確回合';
                        $OK = 1;
                        return 1;
                }
                break;
            case "3":

                switch ($Round) {
                    case "3412":
                        echo '正確回合';

                        if ($P3 == '14' or $P3 == '13' and $P1 == '13' and $P2 == '13' and $P4 == '13') {
                            $OK = 0;
                            return 0;
                        } else {
                            echo '錯誤手牌';
                            $OK = 1;
                            return 1;
                        }
                        break;
                    default:
                        echo '不正確回合';
                        $OK = 1;
                        return 1;
                }
                break;
            case "4":
                switch ($Round) {
                    case "4123":
                        echo '正確回合';

                        if ($P4 == '14' or $P4 == '13' and $P1 == '13' and $P2 == '13' and $P3 == '13') {
                            $OK = 0;
                            return 0;
                        } else {
                            echo '錯誤手牌';
                            $OK = 1;
                            return 1;
                        }
                        break;
                    default:
                        echo '不正確回合';
                        $OK = 1;
                        return 1;
                }
                break;
            default:
                //echo "Your favorite color is neither red, blue, nor green!";
        }
    }
//check_outCard_round

    public function check_outCard_round($Round, $player)
    {
        $parseData['player'] = $player;
        $out[]               = array();
        switch ($Round) {

            case "1234":
                echo "玩家 1 \n";
                $Round_array = '2341';
                //$key       = "player1" ;
                $key                = "player" . $parseData['player'];
                $out['Round_array'] = $Round_array;
                $out['key']         = $key;
                $out['key2']        = "player2";
                return $out;
                break;
            case "2341":
                echo "玩家 2 \n";
                $Round_array = '3412';
                //$key       = "player2" ;
                $key                = "player" . $parseData['player'];
                $out['Round_array'] = $Round_array;
                $out['key']         = $key;
                $out['key2']        = "player3";
                return $out;
                break;
            case "3412":
                echo "玩家 3 \n";
                $Round_array = '4123';
                //$key       = "player3" ;
                $key                = "player" . $parseData['player'];
                $out['Round_array'] = $Round_array;
                $out['key']         = $key;
                $out['key2']        = "player4";
                return $out;
                break;
            case "4123":
                echo "玩家 4 \n";
                $Round_array = '1234';
                //$key       = "player4" ;
                $key                = "player" . $parseData['player'];
                $out['Round_array'] = $Round_array;
                $out['key']         = $key;
                $out['key2']        = "player1";
                return $out;
                break;
            default:
                //echo "Your favorite color is neither red, blue, nor green!";
        }

    }
}
