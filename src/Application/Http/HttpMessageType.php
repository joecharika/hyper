<?php
/**
 * Hyper v0.7.2-beta.2 (https://hyper.starlight.co.zw)
 * Copyright (c) 2020. Joseph Charika
 * Licensed under MIT (https://github.com/joecharika/hyper/master/LICENSE)
 */

namespace Hyper\Http;


abstract class HttpMessageType
{
    const INFO = 'info';
    const SUCCESS = 'success';
    const DANGER = 'danger';
    const WARNING = 'warning';
}