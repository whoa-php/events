<?php namespace Limoncello\Passport\Contracts\Entities;

/**
 * Copyright 2015-2017 info@neomerx.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use DateTime;

/**
 * @package Limoncello\Passport
 */
interface TokenInterface
{
    /**
     * @return int
     */
    public function getIdentifier(): int;

    /**
     * @return string
     */
    public function getClientIdentifier(): string;

    /**
     * @return int
     */
    public function getUserIdentifier(): int;

    /**
     * @return string|null
     */
    public function getCode();

    /**
     * @return DateTime|null
     */
    public function getCodeCreatedAt();

    /**
     * @return string|null
     */
    public function getValue();

    /**
     * @return string|null
     */
    public function getType();

    /**
     * @return string[]
     */
    public function getTokenScopeStrings(): array;

    /**
     * @return DateTime|null
     */
    public function getValueCreatedAt();

    /**
     * @return string|null
     */
    public function getRefreshValue();

    /**
     * @return DateTime|null
     */
    public function getRefreshCreatedAt();
}