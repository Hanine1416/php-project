<?php

/*
 * This file is part of the Inspection Copy.
 * Copyright (C) 2019 Elsevier.
 * Created by mobelite.
 *
 * Date: 4/11/18
 * Time: 17:38
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace MainBundle\Entity;

class BookInstitutionRequest
{
    /**
     * @var string $institutionId
     */
    private $institutionId;

    /**
     * @var string $courseName
     */
    private $courseName;
    /**
     * @var string $course
     */
    private $course;

    /**
     * @var string $studentsNumber
     */
    private $studentsNumber;

    /**
     * @var string $startDate
     */
    private $startDate;

    /**
     * @var string $endDate
     */
    private $endDate;

    /**
     * @var string $recLevel
     */
    private $recLevel;

    /**
     * @var string $currentUsedBook
     */
    private $currentUsedBook;

    /**
     * @var string $bookUsedReason
     */
    private $bookUsedReason;

    /**
     * @var string $courseLevel
     */
    private $courseLevel;

    /**
     * @var string $courseCode
     */
    private $courseCode;

    /**
     * @return string
     */
    public function getCourseName(): ?string
    {
        return $this->courseName;
    }

    /**
     * @param string $courseName
     */
    public function setCourseName(string $courseName)
    {
        $this->courseName = $courseName;
    }

    /**
     * @return string
     */
    public function getCourse(): ?string
    {
        return $this->course;
    }

    /**
     * @param string $course
     */
    public function setCourse(string $course)
    {
        $this->course = $course;
    }

    /**
     * @return integer
     */
    public function getStudentsNumber(): ?int
    {
        return $this->studentsNumber;
    }

    /**
     * @param integer $studentNumber
     */
    public function setStudentsNumber(?int $studentNumber)
    {
        $this->studentsNumber = $studentNumber;
    }

    /**
     * @return string
     */
    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     */
    public function setStartDate(string $startDate)
    {
        $dateObj = explode('-', $startDate);
        $this->startDate = date(DATE_ATOM, mktime(0, 0, 0, $dateObj[1], $dateObj[0], $dateObj[2]));
    }

    /**
     * @return string
     */
    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    /**
     * @param string $endDate
     */
    public function setEndDate(string $endDate)
    {
        $dateObj = explode('-', $endDate);
        $this->endDate = date(DATE_ATOM, mktime(0, 0, 0, $dateObj[1], $dateObj[0], $dateObj[2]));
    }

    /**
     * @return string
     */
    public function getBookUsedReason(): ?string
    {
        return $this->bookUsedReason;
    }

    /**
     * @param string $bookUsedReason
     */
    public function setBookUsedReason(string $bookUsedReason): void
    {
        $this->bookUsedReason = $bookUsedReason;
    }

    /**
     * @return string
     */
    public function getCourseLevel(): ?string
    {
        return $this->courseLevel;
    }

    /**
     * @param string $courseLevel
     */
    public function setCourseLevel(string $courseLevel): void
    {
        $this->courseLevel = $courseLevel;
    }

    /**
     * @return string
     */
    public function getCurrentUsedBook(): ?string
    {
        return $this->currentUsedBook?$this->currentUsedBook:'';
    }

    /**
     * @param string $currentUsedBook
     */
    public function setCurrentUsedBook(?string $currentUsedBook): void
    {
        $this->currentUsedBook = $currentUsedBook;
    }

    /**
     * @return string
     */
    public function getCourseCode(): ?string
    {
        return $this->courseCode;
    }

    /**
     * @param string $courseCode
     */
    public function setCourseCode(string $courseCode): void
    {
        $this->courseCode = $courseCode;
    }

    /**
     * @return string
     */
    public function getInstitutionId(): ?string
    {
        return $this->institutionId;
    }

    /**
     * @param string $institutionId
     */
    public function setInstitutionId(string $institutionId): void
    {
        $this->institutionId = $institutionId;
    }

    /**
     * @return string
     */
    public function getRecLevel(): ?string
    {
        return $this->recLevel;
    }

    /**
     * @param string $recLevel
     */
    public function setRecLevel(string $recLevel): void
    {
        $this->recLevel = $recLevel;
    }
}
