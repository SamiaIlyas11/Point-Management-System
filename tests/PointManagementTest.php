class PointManagementTest extends TestCase
{
    public static function suite()
    {
        $suite = new \PHPUnit\Framework\TestSuite('Point Management Test Suite');
        $suite->addTestSuite(StudentLoginTest::class);
        $suite->addTestSuite(AdminLoginTest::class);
        $suite->addTestSuite(StudentAdditionTest::class);
        $suite->addTestSuite(DriverAdditionTest::class);
        $suite->addTestSuite(StudentFormTest::class);
        $suite->addTestSuite(StudentDataTest::class);
        $suite->addTestSuite(DriverDataTest::class);
        $suite->addTestSuite(StudentPortalLoginTest::class);
        $suite->addTestSuite(FeeChallanTest::class);
        $suite->addTestSuite(PDFChallanGeneratorTest::class);
        $suite->addTestSuite(LocationTrackingTest::class);
        return $suite;
    }
}