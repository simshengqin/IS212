<?php

class BidStatus {
    // property declaration
    private $round;
    private $status;

    public function __construct($round, $status)
    {
        $this->round = $round;
        $this->status = $status;
    }

    public function getRound()
    {
      return $this->round;
    }

    public function getStatus()
    {
      return $this->status;
    }
}

?>
