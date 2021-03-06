<?php

namespace Runalyze\Calculation\Trimp;

use Runalyze\Athlete;
use Runalyze\Profile\Athlete\Gender;
/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2014-10-10 at 23:11:34.
 */
class CalculatorTest extends \PHPUnit_Framework_TestCase {

	private function hrData() {
		return array(
			120 => 30,
			125 => 60,
			130 => 120,
			135 => 120,
			140 => 300,
			145 => 300,
			150 => 240,
			155 => 30,
			160 => 30,
			165 => 120,
			170 => 30
		);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testException() {
		new Calculator(new Athlete(), array());
	}

	public function testGenderIndependency() {
		$data = array(
			1 => 60,
			Calculator::DEFAULT_HR_MAX => 60
		);

		$TrimpMale = new Calculator(new Athlete(Gender::MALE), $data);
		$TrimpFemale = new Calculator(new Athlete(Gender::FEMALE), $data);
		$TrimpNone = new Calculator(new Athlete(Gender::NONE), $data);

		$this->assertEquals( $TrimpMale->value(), $TrimpFemale->value(), 'Trimp dismatch: male and female', 0.01*$TrimpMale->value() );
		$this->assertEquals( $TrimpMale->value(), $TrimpNone->value(), 'Trimp dismatch: male and none', 0.01*$TrimpMale->value() );
		$this->assertEquals( $TrimpFemale->value(), $TrimpNone->value(), 'Trimp dismatch: female and none', 0.01*$TrimpFemale->value() );
	}

	/**
	 * For a different HRmax, the results should at least be similar
	 */
	public function testHRmaxIndependency() {
		$adaptedData = array();
		$dataToAdapt = $this->hrData();
		foreach ($dataToAdapt as $hr => $t) {
			$adaptedData[$hr*180/Calculator::DEFAULT_HR_MAX] = $t;
		}

		$WithHRmaxDefault = new Calculator(new Athlete(null, Calculator::DEFAULT_HR_MAX), $this->hrData());
		$WithHRmaxAdapted = new Calculator(new Athlete(null, 180), $adaptedData);
		$WithoutHRmax = new Calculator(new Athlete(), $this->hrData());

		$this->assertEquals( $WithHRmaxDefault->value(), $WithoutHRmax->value() );
		$this->assertEquals( $WithHRmaxDefault->value(), $WithHRmaxAdapted->value(), 'Trimp dismatch for adapted data', 0.1*$WithHRmaxDefault->value() );
	}

	/**
	 * For a different HRrest, the results should at least be similar
	 */
	public function testHRrestIndependency() {
		$adaptedData = array();
		$dataToAdapt = $this->hrData();
		foreach ($dataToAdapt as $hr => $t) {
			$adaptedData[$hr - 0.5*(Calculator::DEFAULT_HR_REST - 40)] = $t;
		}

		$WithHRrestDefault = new Calculator(new Athlete(null, Calculator::DEFAULT_HR_MAX, Calculator::DEFAULT_HR_REST), $this->hrData());
		$WithHRrestAdapted = new Calculator(new Athlete(null, Calculator::DEFAULT_HR_MAX, 40), $adaptedData);
		$WithoutHRrest = new Calculator(new Athlete(), $this->hrData());

		$this->assertEquals( $WithHRrestDefault->value(), $WithoutHRrest->value() );
		$this->assertEquals( $WithHRrestDefault->value(), $WithHRrestAdapted->value(), 'Trimp dismatch for adapted data', 0.1*$WithHRrestDefault->value() );
	}

	public function testSmallValues() {
		$Trimp = new Calculator(new Athlete(), array(1 => 60*60));

		$this->assertEquals( 0, round($Trimp->value()) );
	}

	public function testExponentialIncrease() {
		$TrimpLow = new Calculator(new Athlete(), array(
			130 => 60*60
		));
		$TrimpHigh = new Calculator(new Athlete(), array(
			120 => 50*60,
			180 => 10*60
		));

		// Make sure that it's not only an epsilon
		$delta = 1.02;
		$this->assertTrue( $TrimpHigh->value() > $TrimpLow->value()*$delta, sprintf('Exponential increase failed: %s has to be larger than %s', $TrimpHigh->value(), $TrimpLow->value()) );
	}

	/**
	 * @see http://fellrnr.com/wiki/TRIMP#Worked_Example
	 */
	public function testReferenceExampleWithoutHRrest() {

		// Fellrnr's example is with HRrest (max = 200, rest = 40, avg = 130)
		$Trimp = new Calculator(new Athlete(
			Gender::MALE,
			160,
			0
		), array(
			90 => 30*60
		));

		$this->assertEquals( 32, $Trimp->value(), '', 0.01*32 );
	}

	/**
	 * @see http://fellrnr.com/wiki/TRIMP#Worked_Example
	 */
	public function testReferenceExample() {

		$Trimp = new Calculator(new Athlete(
			Gender::MALE,
			200,
			40
		), array(
			130 => 30*60
		));

		$this->assertEquals( 32, $Trimp->value(), '', 0.01*32 );
	}

}
