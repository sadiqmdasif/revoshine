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

class ApnsFcmOptions extends \_PhpScoper01187d35592a\Google\Model
{
    /**
     * @var string
     */
    public $analyticsLabel;
    /**
     * @var string
     */
    public $image;
    /**
     * @param string
     */
    public function setAnalyticsLabel($analyticsLabel)
    {
        $this->analyticsLabel = $analyticsLabel;
    }
    /**
     * @return string
     */
    public function getAnalyticsLabel()
    {
        return $this->analyticsLabel;
    }
    /**
     * @param string
     */
    public function setImage($image)
    {
        $this->image = $image;
    }
    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(ApnsFcmOptions::class, '_PhpScoper01187d35592a\\Google_Service_FirebaseCloudMessaging_ApnsFcmOptions');
