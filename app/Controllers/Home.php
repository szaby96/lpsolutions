<?php

namespace App\Controllers;

use App\Models\GroupModel;
use App\Models\UserModel;

class Home extends BaseController
{
    /**
     * Shows the main page
     *
     * @return void
     */
    public function index()
    {
        $groupModel = new GroupModel();
        $groups = $groupModel->findAll();

        $userModel = new UserModel();
        $users = $userModel->findAll();

        $this->addUserGroupName($users, $groups);

        $errorMessages = $this->session->getFlashdata('errorMessages');

        return view('test_task', [
            'groups' => $groups,
            'users' => $users,
            'randomGroup' => count($groups) > 0 ? $groups[array_rand($groups)] : null,
            'randomUser' => count($users) > 0 ? $users[array_rand($users)] : null,
            'errorMessages' => $errorMessages,
        ]);
    }

    /**
     * Save user data
     *
     * @return void
     */
    public function saveUser()
    {
        $firstName = trim($this->request->getPost('firstname'));
        $lastName = trim($this->request->getPost('lastname'));
        $email = $this->generateEmail(strtolower($firstName), strtolower($lastName));
        $groupId = $this->getUserGroupId(strtolower($firstName), strtolower($lastName));

        $userModel = new UserModel();
        $userModel->insert([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'group_id' => $groupId,
        ]);

        return $this->response->redirect(site_url('/'));
    }

    /**
     * Save group data
     *
     * @return void
     */
    public function saveGroup()
    {
        $groupName = $this->request->getPost('group_name');
        $groupCode = $this->request->getPost('group_code');

        if ($this->validateGroupData($groupName, $groupCode)) {
            $groupModel = new GroupModel();
            $groupModel->insert([
                'name' => $groupName,
                'code' => $groupCode,
            ]);
        } else {
            $this->session->setFlashdata('errorMessages', $this->getGroupErrorMessages($groupName, $groupCode));
        }

        return $this->response->redirect(site_url('/'));
    }

    /**
     *  Validates the group data
     *
     * @param string $groupName Group Name
     * @param string $groupCode Group Code
     *
     * @return bool
     */
    private function validateGroupData(string $groupName, string $groupCode): bool
    {
        return $this->validateGroupName($groupName) && $this->isGroupCodeContainsOnlyLetters($groupCode) && $this->validateGroupCodeLength($groupCode) && !$this->isGroupCodeExists($groupCode);
    }

    private function getGroupErrorMessages(string $groupName, string $groupCode): array
    {
        $errorMessages = [];
        if ($this->validateGroupName($groupName)) {
            $errorMessages[] = 'Group Name is required.';
        }
        if ($this->isGroupCodeContainsOnlyLetters($groupCode)) {
            $errorMessages[] = 'Group Code can only contain letters.';
        }
        if ($this->validateGroupCodeLength($groupCode)) {
            $errorMessages[] = 'Group Code must be 4 characters long.';
        }
        if ($this->isGroupCodeExists($groupCode)) {
            $errorMessages[] = 'Group Code is already exists.';
        }

        return $errorMessages;
    }

    /**
     * Validates the group name string
     *
     * @param string $groupName Group Name
     *
     * @return bool
     */
    private function validateGroupName(string $groupName): bool
    {
        return strlen(trim($groupName)) > 0;
    }

    /**
     * Validates Group Code characters
     *
     * @param string $groupCode Group Code
     *
     * @return bool
     */
    private function isGroupCodeContainsOnlyLetters(string $groupCode): bool
    {
        return preg_match('/[^A-Za-z]*/', $groupCode);
    }

    /**
     * Validates the length of the group code
     *
     * @param string $groupCode Group Code
     *
     * @return bool
     */
    private function validateGroupCodeLength(string $groupCode): bool
    {
        return strlen($groupCode) === 4;
    }

    /**
     * Checks if the code is exists
     *
     * @param string $groupCode Group Code
     *
     * @return bool
     */
    private function isGroupCodeExists(string $groupCode): bool
    {
        $groupModel = new GroupModel();

        return count($groupModel->builder()->where('code', $groupCode)->get()->getResult('array')) > 0;
    }

    /**
     * Generates user's email
     *
     * @param string $firstName First Name
     * @param string $lastName Last Name
     *
     * @return string
     */
    private function generateEmail(string $firstName, string $lastName): string
    {
        $maxExecution = 100;
        $counter = 0;
        do {
            $localFirstName = $firstName;
            $localLastName = $lastName;
            $firstNameLength = strlen($localFirstName);
            $lastNameLength = strlen($localLastName);
            $generatedFirstNameNumber = '';
            $generatedLastNameNumber = '';

            if ($firstNameLength < 5) {
                $generatedFirstNameNumber = rand(pow(10, 4 - $firstNameLength), pow(10, 5 - $firstNameLength) - 1);
                $localFirstName = sprintf('%s%d', $localFirstName, $generatedFirstNameNumber);
            }
            if ($lastNameLength < 5) {
                $generatedLastNameNumber = rand(pow(10, 4 - $lastNameLength), pow(10, 5 - $lastNameLength) - 1);
                $localLastName = sprintf('%s%d', $localLastName, $generatedLastNameNumber);
            }

            $generatedEmail = sprintf('%s.%s@example.hu', $localFirstName, $localLastName);
            if (!$this->isEmailExists($generatedEmail)) {
                return $generatedEmail;
            }

            $generatedEmail = sprintf('%s.%s@example.hu', $localLastName, $localFirstName);
            if (!$this->isEmailExists($generatedEmail)) {
                return $generatedEmail;
            }

            $localFirstName = $this->createAnagram($firstName) . $generatedFirstNameNumber;
            $localLastName = $this->createAnagram($lastName) . $generatedLastNameNumber;
            $generatedEmail = sprintf('%s.%s@example.hu', $localFirstName, $localLastName);
            if (!$this->isEmailExists($generatedEmail)) {
                return $generatedEmail;
            }

            $counter++;
        } while ($counter < $maxExecution);

        return sprintf('%s.%s@example.hu', rand(0, 99999), rand(0, 99999));
    }

    /**
     * Checks if email exists
     *
     * @param string $email
     *
     * @return boolean
     */
    private function isEmailExists(string $email): bool
    {
        $userModel = new UserModel();

        return count($userModel->builder()->where('email', $email)->get()->getResult('array')) > 0;
    }

    /**
     * Creates an anagram from the given string
     *
     * @param string $string
     *
     * @return string
     */
    private function createAnagram(string $string): string
    {
        $baseString = str_split($string);
        $newString = '';

        for ($i = 0; $i < strlen($string) - 1; $i++) {
            $randomKey = rand(0, count($baseString) - 1);
            $newString .= $baseString[$randomKey];
            unset($baseString[$randomKey]);
            $baseString = array_values($baseString);
        }

        return $newString;
    }

    /**
     * Finds the perfect group id for the user
     *
     * @param string $firstName
     * @param string $lastName
     *
     * @return void
     */
    private function getUserGroupId(string $firstName, string $lastName)
    {
        $groupModel = new GroupModel();
        $groups = $groupModel->findAll();
        $groupsData = [];
        $groupsWordCount = [];

        if (count($groups) === 0) {
            return 0;
        }

        foreach ($groups as $groupEntity) {
            foreach (str_split($groupEntity['code']) as $codeChar) {
                $groupsData[$groupEntity['id']][strtolower($codeChar)] = str_contains($firstName . $lastName, strtolower($codeChar));
            }
        }

        foreach ($groupsData as $groupId => $charsData) {
            $groupsWordCount[$groupId] = count(array_filter($charsData));
        }

        arsort($groupsWordCount);

        $possibleGroupIds = [];
        $firstCharCount = $groupsWordCount[array_key_first($groupsWordCount)];

        if ($firstCharCount === 0) {
            return 0;
        }

        foreach ($groupsWordCount as $groupId => $charsCount) {
            if ($charsCount !== $firstCharCount) {
                break;
            }

            $possibleGroupIds[$groupId] = $groupId;
        }

        if (count($possibleGroupIds) === 1) {
            return $possibleGroupIds[array_key_first($possibleGroupIds)];
        }

        $groupsWithMembers = $groupModel->getGroupsWithFewestUsers($possibleGroupIds);
        $firstUserCount = $groupsWithMembers[array_key_first($groupsWithMembers)]['countIds'];
        $leastMemberGroups = [];

        foreach ($groupsWithMembers as $groupWithMembers) {
            if ((int) $firstUserCount !== (int) $groupWithMembers['countIds']) {
                break;
            }
            $leastMemberGroups[] = $groupWithMembers['group_id'];
        }

        if (count($leastMemberGroups) === 0) {
            return 0;
        }

        if (count($leastMemberGroups) === 1) {
            return reset($leastMemberGroups);
        }

        return array_rand($leastMemberGroups);
    }

    /**
     * Add the user's group's name to the users array
     *
     * @param array $users
     * @param array $groups
     *
     * @return void
     */
    private function addUserGroupName(array &$users, array $groups)
    {
        $groupNamesArray = $this->createGroupNamesFlatArray($groups);

        foreach ($users as &$userData) {
            $userData['groupName'] = $groupNamesArray[$userData['group_id']] ?? 'Without Group';
        }
    }

    /**
     * Gives an array with the group id and name pair
     *
     * @param array $groups
     *
     * @return array
     */
    private function createGroupNamesFlatArray(array $groups): array
    {
        $groupNames = [];

        foreach ($groups as $groupData) {
            $groupNames[$groupData['id']] = $groupData['name'];
        }

        return $groupNames;
    }
}
