<?php namespace Limoncello\Passport\Entities;

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

use DateTimeImmutable;
use Limoncello\Passport\Contracts\Entities\TokenInterface;

/**
 * @package Limoncello\Passport
 */
abstract class Token implements TokenInterface
{
    /**
     * @return string
     */
    abstract protected function getListSeparator(): string;

    /**
     * @return string
     */
    abstract protected function getDbDateFormat(): string;

    /** Field name */
    const FIELD_ID = 'id_token';

    /** Field name */
    const FIELD_ID_CLIENT = Client::FIELD_ID;

    /** Field name */
    const FIELD_ID_USER = 'id_user';

    /** Field name */
    const FIELD_TOKEN_SCOPE_LIST = 'token_scope_list';

    /** Field name */
    const FIELD_IS_ENABLED = 'is_enabled';

    /** Field name */
    const FIELD_CODE = 'code';

    /** Field name */
    const FIELD_VALUE = 'value';

    /** Field name */
    const FIELD_TYPE = 'type';

    /** Field name */
    const FIELD_REFRESH = 'refresh';

    /** Field name */
    const FIELD_CODE_CREATED_AT = 'code_created_at';

    /** Field name */
    const FIELD_VALUE_CREATED_AT = 'value_created_at';

    /** Field name */
    const FIELD_REFRESH_CREATED_AT = 'refresh_created_at';

    /**
     * @var int
     */
    private $identifierField;

    /**
     * @var string
     */
    private $clientIdentifierField;

    /**
     * @var int
     */
    private $userIdentifierField;

    /**
     * @var string[]
     */
    private $tokenScopeStrings;

    /**
     * @var string|null
     */
    private $codeField;

    /**
     * @var string|null
     */
    private $valueField;

    /**
     * @var string|null
     */
    private $typeField;

    /**
     * @var string|null
     */
    private $refreshValueField;

    /**
     * @var DateTimeImmutable|null
     */
    private $codeCreatedAtField = null;

    /**
     * @var DateTimeImmutable|null
     */
    private $valueCreatedAtField = null;

    /**
     * @var DateTimeImmutable|null
     */
    private $refreshCreatedAtField = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if ($this->hasDynamicProperty(static::FIELD_ID) === true) {
            $this
                ->setIdentifier((int)$this->{static::FIELD_ID})
                ->setClientIdentifier($this->{static::FIELD_ID_CLIENT})
                ->setUserIdentifier((int)$this->{static::FIELD_ID_USER})
                ->setCode($this->{static::FIELD_CODE})
                ->setType($this->{static::FIELD_TYPE})
                ->setValue($this->{static::FIELD_VALUE})
                ->setRefreshValue($this->{static::FIELD_REFRESH})
                ->parseTokenScopeList(
                    $this->hasDynamicProperty(static::FIELD_TOKEN_SCOPE_LIST) === true ?
                        $this->{static::FIELD_TOKEN_SCOPE_LIST} : ''
                );
        }
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): int
    {
        return $this->identifierField;
    }

    /**
     * @param int $identifier
     *
     * @return Token
     */
    public function setIdentifier(int $identifier): Token
    {
        $this->identifierField = $identifier;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getClientIdentifier(): string
    {
        return $this->clientIdentifierField;
    }

    /**
     * @param string $identifier
     *
     * @return Token
     */
    public function setClientIdentifier(string $identifier): Token
    {
        $this->clientIdentifierField = $identifier;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUserIdentifier(): int
    {
        return $this->userIdentifierField;
    }

    /**
     * @param int $identifier
     *
     * @return Token
     */
    public function setUserIdentifier(int $identifier): Token
    {
        $this->userIdentifierField = $identifier;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTokenScopeStrings(): array
    {
        return $this->tokenScopeStrings;
    }

    /**
     * @param string $uriList
     *
     * @return Token
     */
    public function parseTokenScopeList(string $uriList): Token
    {
        return $this->setTokenScopeStrings(
            empty($uriList) === true ? [] : explode($this->getListSeparator(), $uriList)
        );
    }

    /**
     * @param string[] $tokenScopeStrings
     *
     * @return Token
     */
    public function setTokenScopeStrings(array $tokenScopeStrings): Token
    {
        $this->tokenScopeStrings = $tokenScopeStrings;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->codeField;
    }

    /**
     * @param string|null $code
     *
     * @return Token
     */
    public function setCode(string $code = null): Token
    {
        $this->codeField = $code;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->valueField;
    }

    /**
     * @param string|null $value
     *
     * @return Token
     */
    public function setValue(string $value = null): Token
    {
        $this->valueField = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->typeField;
    }

    /**
     * @param string|null $type
     *
     * @return Token
     */
    public function setType(string $type = null): Token
    {
        $this->typeField = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRefreshValue()
    {
        return $this->refreshValueField;
    }

    /**
     * @param string|null $refreshValue
     *
     * @return Token
     */
    public function setRefreshValue(string $refreshValue = null): Token
    {
        $this->refreshValueField = $refreshValue;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCodeCreatedAt()
    {
        if ($this->codeCreatedAtField === null && ($codeCreatedAt = $this->{static::FIELD_CODE_CREATED_AT}) !== null) {
            $this->codeCreatedAtField = $this->parseDateTime($codeCreatedAt);
        }

        return $this->codeCreatedAtField;
    }

    /**
     * @param DateTimeImmutable $codeCreatedAt
     *
     * @return Scope
     */
    public function setCodeCreatedAt(DateTimeImmutable $codeCreatedAt): Scope
    {
        $this->codeCreatedAtField = $codeCreatedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValueCreatedAt()
    {
        if ($this->valueCreatedAtField === null &&
            ($tokenCreatedAt = $this->{static::FIELD_VALUE_CREATED_AT}) !== null
        ) {
            $this->valueCreatedAtField = $this->parseDateTime($tokenCreatedAt);
        }

        return $this->valueCreatedAtField;
    }

    /**
     * @param DateTimeImmutable $valueCreatedAt
     *
     * @return Scope
     */
    public function setValueCreatedAt(DateTimeImmutable $valueCreatedAt): Scope
    {
        $this->valueCreatedAtField = $valueCreatedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRefreshCreatedAt()
    {
        if ($this->refreshCreatedAtField === null &&
            ($tokenCreatedAt = $this->{static::FIELD_VALUE_CREATED_AT}) !== null
        ) {
            $this->refreshCreatedAtField = $this->parseDateTime($tokenCreatedAt);
        }

        return $this->refreshCreatedAtField;
    }

    /**
     * @param DateTimeImmutable $refreshCreatedAt
     *
     * @return Scope
     */
    public function setRefreshCreatedAt(DateTimeImmutable $refreshCreatedAt): Scope
    {
        $this->refreshCreatedAtField = $refreshCreatedAt;

        return $this;
    }

    /**
     * @param string $createdAt
     *
     * @return DateTimeImmutable
     */
    protected function parseDateTime(string $createdAt): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat($this->getDbDateFormat(), $createdAt);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function hasDynamicProperty(string $name): bool
    {
        return property_exists($this, $name);
    }
}