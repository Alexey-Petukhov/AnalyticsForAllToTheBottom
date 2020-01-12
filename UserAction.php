<?php

class UserAction
{
    protected $_date;
    protected $_time;
    protected $_uniqActionKey;
    protected $_userIP;
    protected $_link;
    private $category;
    private $product;

    function __construct($date, $time, $uniqActionKey, $userIP, $link, $category, $product)
    {
        $this->_date = $date;
        $this->_time = $time;
        $this->_uniqActionKey = $uniqActionKey;
        $this->_userIP = $userIP;
        $this->_link = $link;
        $this->category = $category;
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category): void
    {
        $this->category = $category;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product): void
    {
        $this->product = $product;
    }
    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->_link;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->_time;
    }

    /**
     * @return mixed
     */
    public function getUniqActionKey()
    {
        return $this->_uniqActionKey;
    }

    /**
     * @return mixed
     */
    public function getUserIP()
    {
        return $this->_userIP;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date): void
    {
        $this->_date = $date;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link): void
    {
        $this->_link = $link;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time): void
    {
        $this->_time = $time;
    }

    /**
     * @param mixed $userIP
     */
    public function setUserIP($userIP): void
    {
        $this->_userIP = $userIP;
    }

    /**
     * @param mixed $uniqActionKey
     */
    public function setUniqActionKey($uniqActionKey): void
    {
        $this->_uniqActionKey = $uniqActionKey;
    }
}

?>