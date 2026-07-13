<?php

namespace App\Tests\Entity;

use App\Entity\BugReport;
use App\Enum\BugStatus;
use PHPUnit\Framework\TestCase;

final class BugReportTest extends TestCase
{
    public function testClosedAtIsNotResetWhenStatusStaysClosed(): void
    {
        $bugReport = new BugReport();

        $bugReport->setStatus(BugStatus::Closed);
        $closedAt = $bugReport->getClosedAt();

        self::assertNotNull($closedAt);

        $bugReport->setStatus(BugStatus::Closed);

        self::assertSame($closedAt, $bugReport->getClosedAt());
    }

    public function testClosedAtIsClearedWhenBugIsReopened(): void
    {
        $bugReport = new BugReport();

        $bugReport->setStatus(BugStatus::Rejected);
        self::assertNotNull($bugReport->getClosedAt());

        $bugReport->setStatus(BugStatus::InProgress);

        self::assertNull($bugReport->getClosedAt());
    }
}
