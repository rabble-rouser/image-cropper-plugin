<?php
namespace Craft;

class ImageCropper_ResponseModel extends BaseModel
{
    public function defineAttributes()
    {
        return array(
            'success'       =>  AttributeType::Bool,
            'message'       =>  AttributeType::String,
            'context'       =>  AttributeType::Mixed,
            'data'          =>  AttributeType::Mixed
        );
    }

    public function isSuccess()
    {
        return $this->success;
    }

    public function isError()
    {
        return $this->success == false || empty($this->success);
    }

    public function hasTransaction()
    {
        return (isset($this->transaction) && !empty($this->transaction));
    }

    public function hasData()
    {
        return (isset($this->data) && !empty($this->data));
    }

    public function hasMessage()
    {
        return (isset($this->message) && !empty($this->message));
    }

    public function setSuccess()
    {
        $this->success = true;
    }

    public function setError()
    {
        $this->success = false;
    }

    public function setMessage($message = '')
    {
        $this->message = $message;
    }

    public function setSuccessMessage($message)
    {
        $this->setSuccess();
        $this->setMessage($message);
    }

    public function setErrorMessage($message)
    {
        $this->setError();
        $this->setMessage($message);
    }

    public function getMessage()
    {
        return $this->message;
    }
}