@format @format_qmultopics
Feature: See various assessment badges
  As a student
  In order to start a quiz with confidence
  I need to see a badge if there is a time limit and a badge for an attempt

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher  | Teacher   | One      | teacher@example.com |
      | student  | Student   | One      | student@example.com  |
    And the following "courses" exist:
      | fullname | shortname | format     | coursedisplay | numsections |
      | Course 1 | C1        | qmultopics | 0             | 5           |
    And the following "course enrolments" exist:
      | user     | course | role            |
      | teacher  | C1     | editingteacher  |
      | student  | C1     | student         |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name  | questiontext               |
      | Test questions   | truefalse   | TF1   | Text of the first question |
    And the following "activities" exist:
      | activity   | name   | intro              | course | idnumber | timeclose  | section |
      | quiz       | Quiz 1 | Quiz 1 description | C1     | quiz1    | 1609063560 | 1       |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | TF1      | 1    |

  @javascript
  Scenario: As a student see a badge with a time limit and a badge with no attempt
    When I log in as "student"
    And I am on "Course 1" course homepage
    Then I should see "Due 27 December 2020"
    And I should see "Not attempted"

  @javascript
  Scenario: As a teacher see a badge with a time limit and a badge with no attempt
    When I log in as "teacher"
    And I am on "Course 1" course homepage
    Then I should see "Due 27 December 2020"
#    And I should see "0 of 1 Attempted"
  