<?php

/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
namespace _PhpScoper01187d35592a\Google\Service\FirebaseCloudMessaging;

class WebpushConfig extends \_PhpScoper01187d35592a\Google\Model
{
    /**
     * @var string[]
     */
    public $data;
    protected $fcmOptionsType = WebpushFcmOptions::class;
    protected $fcmOptionsDataType = '';
    /**
     * @var string[]
     */
    public $headers;
    /**
     * @var array[]
     */
    public $notification;
    /**
     * @param string[]
     */
    public function setData($data)
    {
        $this->data = $data;
    }
    /**
     * @return string[]
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * @param WebpushFcmOptions
     */
    public function setFcmOptions(WebpushFcmOptions $fcmOptions)
    {
        $this->fcmOptions = $fcmOptions;
    }
    /**
     * @return WebpushFcmOptions
     */
    public function getFcmOptions()
    {
        return $this->fcmOptions;
    }
    /**
     * @param string[]
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }
    /**
     * @return string[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    /**
     * @param array[]
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
    }
    /**
     * @return array[]
     */
    public function getNotification()
    {
        return $this->notification;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(WebpushConfig::class, '_PhpScoper01187d35592a\\Google_Service_FirebaseCloudMessaging_WebpushConfig');
