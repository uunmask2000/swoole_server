<?php
class Carbox
{

    public $cardList;
    public $cardNum;
    public $beach;
    public function __construct()
    {

        $this->cardList = [];
        $this->cardNum  = 13;
        $this->init();
        $this->beach = $this->cardList;
    }

    public function __destruct() {

    }

    public function init()
    {
        $this->initList();
        $this->shuffleCard();
        // print_r($this->cardList);
    }

    /**
     * 初始化牌組
     */
    private function initList()
    {
        /**
        「萬子/萬」 = 開頭為1
        「筒子/餅」 = 開頭為2
        「索子/條」 = 開頭為3

        「東、南、西、北」「風牌」 = 開頭為41 42 43 44
        「中、發、白」「三元牌」 =  開頭為45 46 47
         **/

        $wanList   = [11, 12, 13, 14, 15, 16, 17, 18, 19];
        $tiaoList  = [21, 22, 23, 24, 25, 26, 27, 28, 29];
        $tongList  = [31, 32, 33, 34, 35, 36, 37, 38, 39];
        $fengList  = [41, 42, 43, 44];
        $otherList = [45, 46, 47];

        for ($i = 0; $i < 4; $i++) {
            $this->cardList = array_merge($this->cardList, $wanList);
            $this->cardList = array_merge($this->cardList, $tiaoList);
            $this->cardList = array_merge($this->cardList, $tongList);
            $this->cardList = array_merge($this->cardList, $fengList);
            $this->cardList = array_merge($this->cardList, $otherList);
        }

        // var_dump($this->cardList);
    }

    /**
     * 洗牌
     */
    private function shuffleCard()
    {
        shuffle($this->cardList);
    }

    /**
     * 發牌
     * return Array [發給玩家的四副牌]
     */
    public function dealCard()
    {
        //echo sizeof($this->cardList);
        $startArr = $this->cardList;

        $cardArr1 = [];
        $cardArr2 = [];
        $cardArr3 = [];
        $cardArr4 = [];
        $retArr   = [];
        
        for($i = 0; $i < ($this->cardNum * 4); $i++){
            $result = array_shift($this->cardList);
            if(($i % 4) == 0){
                array_push($cardArr1, $result);
            } elseif (($i % 4) == 1) {
                array_push($cardArr2, $result);
            } elseif (($i % 4) == 2) {
                array_push($cardArr3, $result);
            } elseif (($i % 4) == 3) {
                array_push($cardArr4 ,$result);
            }
        }

        $endtArr = $this->cardList;
        // var_dump($this->cardList);
        /*
        $retArr["nonSort"] = array($cardArr1, $cardArr2, $cardArr3, $cardArr4);
        sort($cardArr1);
        sort($cardArr2);
        sort($cardArr3);
        sort($cardArr4);
        $retArr["Sort"] = array($cardArr1, $cardArr2, $cardArr3, $cardArr4);
         */
        sort($cardArr1);
        sort($cardArr2);
        sort($cardArr3);
        sort($cardArr4);
        $retArr = array(
            "startCard" => $startArr,
            "endCard"   => $endtArr,
            "player1"   => $cardArr1,
            "player2"   => $cardArr2,
            "player3"   => $cardArr3,
            "player4"   => $cardArr4,			
        );
        return $retArr;
    }

    

}
