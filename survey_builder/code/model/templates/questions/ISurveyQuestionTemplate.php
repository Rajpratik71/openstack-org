<?php
/**
 * Copyright 2015 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

/**
 * Interface SurveyQuestionTemplate
 */
interface ISurveyQuestionTemplate extends IEntity {

    /**
     * @return string
     */
    public function label();

    /**
     * @return string
     */
    public function name();

    /**
     * @return int
     */
    public function order();

    /**
     * @return bool
     */
    public function isMandatory();

    /**
     * @return bool
     */
    public function isReadOnly();

    /**
     * @return bool
     */
    public function isVisible();

    /**
     * @return bool
     */
    public function isHidden();


    /**
     * @return ISurveyQuestionTemplate[]
     */
    public function getDependsOn();

    /**
     * @return ISurveyQuestionTemplate[]
     */
    public function getDependers();

    /**
     * @return string
     */
    public function Type();

    /**
     * @return ISurveyStepTemplate
     */
    public function step();

    /**
     * @param string $answer_value
     * @return bool
     */
    public function isValidAnswerValue($answer_value);
}