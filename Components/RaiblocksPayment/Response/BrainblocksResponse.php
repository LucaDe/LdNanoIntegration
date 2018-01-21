<?php
namespace LdRaiblocksIntegration\Components\RaiblocksPayment\Response;

class BrainblocksResponse
{
    /** @var string $token */
    private $token;

    /** @var  string $destination */
    private $destination;

    /** @var  string $currency */
    private $currency;

    /** @var  string $amount */
    private $amount;

    /** @var  bool $fulfilled */
    private $fulfilled;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return bool
     */
    public function isFulfilled()
    {
        return $this->fulfilled;
    }

    /**
     * @param bool $fulfilled
     */
    public function setFulfilled($fulfilled)
    {
        $this->fulfilled = $fulfilled;
    }
}