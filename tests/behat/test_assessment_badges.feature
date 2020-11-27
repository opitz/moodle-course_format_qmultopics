@format @format_qmultopics
Feature: See various assessment badges
  As a student
  In order to start a quiz with confidence
  I need to see a badge if there is a time limit and a badge for an attempt

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | student  | Student   | One      | student@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student  | C1     | student |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name  | questiontext               |
      | Test questions   | truefalse   | TF1   | Text of the first question |
    And I log in as "student"
    And I am on "Course 1" course homepage
  @javascript
  Scenario: See a badge with a time limit
    Given the following "activities" exist:
      | activity   | name   | intro              | course | idnumber | timeopen   | timeclose  | timelimit |
      | quiz       | Quiz 1 | Quiz 1 description | C1     | quiz1    | 1606471560 | 1609063560 | 3600      |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
    Then I should see "Due 27 December 2020"
    And I should see "Not attempted"

