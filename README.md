Payment [![Latest Stable Version](https://poser.pugx.org/playbloom/payment/v/stable.png)](https://packagist.org/packages/playbloom/payment) [![Latest Unstable Version](https://poser.pugx.org/playbloom/payment/v/unstable.png)](https://packagist.org/packages/playbloom/payment) [![Total Downloads](https://poser.pugx.org/playbloom/payment/downloads.png)](https://packagist.org/packages/playbloom/payment)
================================================================================================

[![Build Status](https://travis-ci.org/ludofleury/payment.png?branch=master)](https://travis-ci.org/ludofleury/payment)
[![Coverage Status](https://coveralls.io/repos/ludofleury/payment/badge.png)](https://coveralls.io/r/ludofleury/payment)
[![Quality Score](https://scrutinizer-ci.com/g/ludofleury/payment/badges/quality-score.png?s=5c807304c9cd3a230c5b1ed1c7d2fb97785790dd)](https://scrutinizer-ci.com/g/ludofleury/payment/)
[![Build Success Rate](https://www.buildheroes.com/projects/payment.png)](https://www.buildheroes.com/projects/payment)

Payment library for card transaction

## Contribute

### Get composer

    curl -s http://getcomposer.org/installer | php

Then install the library dependencies

    php composer.phar install --dev


## Tests

### Unit testing with atoum

Define the .atoum.php configuration file

    cp .atoum.php.dist .atoum.php

Then run the unit test suite

    vendor/bin/atoum -d tests
