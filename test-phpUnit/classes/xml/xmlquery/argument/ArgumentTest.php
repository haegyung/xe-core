<?php

/**
 * Test class for Argument.
 * Generated by PHPUnit on 2011-07-07 at 16:51:29.
 */
class ArgumentTest extends CubridTest {

    public function testErrorMessageIsSent_NotNullCheck(){
        global $lang;
        include(_TEST_PATH_ . "classes/xml/xmlquery/argument/data/en.lang.php");
        $page_argument = new Argument('page', $args->page);
        $page_argument->checkNotNull();
        $this->assertFalse($page_argument->isValid());
        $this->assertEquals("Please input a value for page", $page_argument->getErrorMessage()->message);
    }

    public function testErrorMessageIsSent_MinLengthCheck(){
        global $lang;
        include(_TEST_PATH_ . "classes/xml/xmlquery/argument/data/en.lang.php");

        $args->page = '123';
        $page_argument = new Argument('page', $args->page);
        $page_argument->checkMinLength(6);
        $this->assertFalse($page_argument->isValid());
        $this->assertEquals("Please align the text length of page", $page_argument->getErrorMessage()->message);
    }

    public function testErrorMessageIsSent_MaxLengthCheck(){
        global $lang;
        include(_TEST_PATH_ . "classes/xml/xmlquery/argument/data/en.lang.php");

        $args->page = '123';
        $page_argument = new Argument('page', $args->page);
        $page_argument->checkMaxLength(2);
        $this->assertFalse($page_argument->isValid());
        $this->assertEquals("Please align the text length of page", $page_argument->getErrorMessage()->message);
    }

    /**
     * @todo Implement testGetType().
     */
    public function testGetType() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testSetColumnType().
     */
    public function testSetColumnType() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetName().
     */
    public function testGetName() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetValue().
     */
    public function testGetValue() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetUnescapedValue().
     */
    public function testGetUnescapedValue() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testToString().
     */
    public function testToString() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testEscapeValue().
     */
    public function testEscapeValue() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testIsValid().
     */
    public function testIsValid() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetErrorMessage().
     */
    public function testGetErrorMessage() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testEnsureDefaultValue().
     */
    public function testEnsureDefaultValue() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCheckFilter().
     */
    public function testCheckFilter() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCheckMaxLength().
     */
    public function testCheckMaxLength() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCheckMinLength().
     */
    public function testCheckMinLength() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * Checks that argument is valid after a notnull check when value is not null
     */
    public function testCheckNotNullWhenNotNull() {
        $member_srl_argument = new ConditionArgument('member_srl', 20, 'equal');
        $member_srl_argument->checkNotNull();

        $this->assertEquals(true, $member_srl_argument->isValid());
    }

    /**
     * Checks that argument becomes invalid after a notnull check when value is null
     */
    public function testCheckNotNullWhenNull() {
        $member_srl_argument = new ConditionArgument('member_srl', null, 'equal');
        $member_srl_argument->checkNotNull();

        $this->assertEquals(false, $member_srl_argument->isValid());
    }

    /**
     * Checks that argument value stays the same when both user value and default value are given
     */
    public function testCheckDefaultValueWhenNotNull() {
        $member_srl_argument = new ConditionArgument('member_srl', 20, 'equal');
        $member_srl_argument->ensureDefaultValue(25);

        $this->assertEquals(20, $member_srl_argument->getValue());
    }

    /**
     * Checks that argument value gets set when user value is null and default value is specified
     */
    public function testCheckDefaultValueWhenNull() {
        $member_srl_argument = new ConditionArgument('member_srl', null, 'equal');
        $member_srl_argument->ensureDefaultValue(25);

        $this->assertEquals(25, $member_srl_argument->getValue());
    }

     /**
     * Checks like prefix operation
     */
    public function testCreateConditionValue_LikePrefix() {
        $member_srl_argument = new ConditionArgument('"mid"', 'forum', 'like_prefix');
        $member_srl_argument->createConditionValue();

        $this->assertEquals('\'forum%\'', $member_srl_argument->getValue());
    }

     /**
     * Checks like tail operation
     */
    public function testCreateConditionValue_LikeTail() {
        $member_srl_argument = new ConditionArgument('"mid"', 'forum', 'like_tail');
        $member_srl_argument->createConditionValue();

        $this->assertEquals('\'%forum\'', $member_srl_argument->getValue());
    }

     /**
     * Checks like operation
     */
    public function testCreateConditionValue_Like() {
        $member_srl_argument = new ConditionArgument('"mid"', 'forum', 'like');
        $member_srl_argument->createConditionValue();

        $this->assertEquals('\'%forum%\'', $member_srl_argument->getValue());
    }


     /**
     * Checks in operation
     */
    public function testCreateConditionValue_In_StringValues() {
        $member_srl_argument = new ConditionArgument('"mid"', array('forum', 'board'), 'in');
        $member_srl_argument->createConditionValue();
        $member_srl_argument->setColumnType('varchar');

        $this->assertEquals('(\'forum\',\'board\')', $member_srl_argument->getValue());
    }

     /**
     * Checks in operation
     */
    public function testCreateConditionValue_In_NumericValues() {
        $member_srl_argument = new ConditionArgument('"module_srl"', array(3, 21), 'in');
        $member_srl_argument->setColumnType('number');
        $member_srl_argument->createConditionValue();

        $this->assertEquals('(3,21)', $member_srl_argument->getValue());
    }

    public function testEnsureDefaultValueWithEmptyString(){
        $homepage_argument = new Argument('homepage', '');
        $homepage_argument->ensureDefaultValue('');
        $homepage_argument->checkFilter('homepage');
        if(!$homepage_argument->isValid()) return $homepage_argument->getErrorMessage();
        $homepage_argument->setColumnType('varchar');


        $this->assertEquals('\'\'', $homepage_argument->getValue());
    }

    public function testDefaultValue() {
        $default = new DefaultValue("var", '');
        $this->assertEquals('\'\'', $default->toString());
    }
}

?>
