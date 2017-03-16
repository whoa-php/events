<?php namespace Limoncello\Core\Routing;

/**
 * Copyright 2015-2016 info@neomerx.com (www.neomerx.com)
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

/**
 * @package Limoncello\Core
 */
class Group extends BaseGroup
{
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        list($middleware, $configurators, $factoryWasGiven, $requestFactory, $name) =
            $this->normalizeGroupParameters($parameters);

        if (empty($middleware) === false) {
            $this->setMiddleware($middleware);
        }
        if (empty($configurators) === false) {
            $this->setConfigurators($configurators);
        }
        if (empty($name) === false) {
            $this->setName($name);
        }
        if ($factoryWasGiven === true) {
            $this->setRequestFactory($requestFactory);
        }
    }

    /**
     * @return null
     */
    public function parentGroup()
    {
        // null
        return parent::parentGroup();
    }

    /**
     * @return NestedGroup
     */
    protected function createGroup()
    {
        $group = (new NestedGroup($this))->setHasTrailSlash($this->hasTrailSlash());

        return $group;
    }
}