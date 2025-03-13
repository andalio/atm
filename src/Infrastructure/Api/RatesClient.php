<?php

namespace App\Infrastructure\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Domain\Commission\Exception\ExchangeRateException;

class RatesClient
{
    private const API_URL = 'https://api.exchangeratesapi.io/v1/latest?access_key=XXXXXXXXXXXXXXXXXXXXX&format=1';
    private array $rates = [];

    public function __construct(
        private readonly HttpClientInterface $client
    ) {
        $this->fetchRates();
    }

    private function fetchRates(): void
    {
        try {
//            $response = $this->client->request('GET', self::API_URL);
//            $content = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $response->getContent()));
            $content = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', '{"success":true,"timestamp":1741806483,"base":"EUR","date":"2025-03-12","rates":{"AED":3.998356,"AFN":77.118967,"ALL":99.860562,"AMD":428.298577,"ANG":1.963391,"AOA":996.660712,"ARS":1160.359088,"AUD":1.724205,"AWG":1.959578,"AZN":1.851851,"BAM":1.954542,"BBD":2.199544,"BDT":132.368485,"BGN":1.952486,"BHD":0.410334,"BIF":3170.706482,"BMD":1.088655,"BND":1.453438,"BOB":7.528295,"BRL":6.317027,"BSD":1.089379,"BTC":0.000013178729,"BTN":95.019121,"BWP":14.933666,"BYN":3.565141,"BYR":21337.62989,"BZD":2.188332,"CAD":1.564212,"CDF":3130.97062,"CHF":0.960517,"CLF":0.026641,"CLP":1022.344907,"CNY":7.879519,"CNH":7.882665,"COP":4471.790238,"CRC":544.73952,"CUC":1.088655,"CUP":28.849347,"CVE":110.55283,"CZK":25.033624,"DJF":193.475259,"DKK":7.460555,"DOP":68.259662,"DZD":145.036903,"EGP":55.188583,"ERN":16.329819,"ETB":140.656686,"EUR":1,"FJD":2.496992,"FKP":0.840939,"GBP":0.839646,"GEL":3.021005,"GGP":0.840939,"GHS":16.867167,"GIP":0.840939,"GMD":78.601239,"GNF":9407.583844,"GTQ":8.3931,"GYD":227.484784,"HKD":8.458177,"HNL":27.860979,"HRK":7.536712,"HTG":143.504197,"HUF":400.069088,"IDR":17908.000464,"ILS":3.962403,"IMP":0.840939,"INR":94.90944,"IQD":1425.437528,"IRR":45753.35176,"ISK":146.469017,"JEP":0.840939,"JMD":170.78955,"JOD":0.771845,"JPY":161.477945,"KES":140.737319,"KGS":95.436273,"KHR":4360.366857,"KMF":490.206986,"KPW":979.834925,"KRW":1582.491268,"KWD":0.335394,"KYD":0.902653,"KZT":532.217241,"LAK":23584.503935,"LBP":97489.602077,"LKR":321.672965,"LRD":217.482919,"LSL":19.810512,"LTL":3.214514,"LVL":0.658517,"LYD":5.249099,"MAD":10.529007,"MDL":19.695831,"MGA":5066.83481,"MKD":61.377831,"MMK":2284.73282,"MNT":3778.765332,"MOP":8.712792,"MRU":42.938796,"MUR":49.05474,"MVR":16.810396,"MWK":1887.511258,"MXN":21.99054,"MYR":4.805435,"MZN":69.504968,"NAD":19.810512,"NGN":1664.490475,"NIO":40.038317,"NOK":11.572948,"NPR":151.926286,"NZD":1.901182,"OMR":0.419108,"PAB":1.088655,"PEN":3.990286,"PGK":4.43843,"PHP":62.490825,"PKR":305.213238,"PLN":4.185001,"PYG":8635.688257,"QAR":3.963187,"RON":4.959595,"RSD":116.752094,"RUB":92.750968,"RWF":1537.096502,"SAR":4.082749,"SBD":9.227973,"SCR":15.783294,"SDG":653.847482,"SEK":10.983632,"SGD":1.448425,"SHP":0.855512,"SLE":24.875633,"SLL":22828.548484,"SOS":621.998671,"SRD":39.110087,"STD":22532.95195,"SVC":9.525852,"SYP":14155.006358,"SZL":19.810512,"THB":36.753566,"TJS":11.873864,"TMT":3.807773,"TND":3.35049,"TOP":2.62298,"TRY":39.831422,"TTD":7.400329,"TWD":35.827834,"TZS":2867.553653,"UAH":45.058842,"UGX":3994.954092,"USD":1.088655,"UYU":46.022118,"UZS":14077.538886,"VES":70.850889,"VND":27668.045398,"VUV":134.266334,"WST":3.080949,"XAF":653.609314,"XAG":0.032724,"XAU":0.000371,"XCD":2.946319,"XDR":0.817625,"XOF":653.609314,"XPF":119.331742,"YER":268.589716,"ZAR":19.969066,"ZMK":9799.199231,"ZMW":31.114059,"ZWL":350.546333}}'));
            $data = json_decode($content, true);

            if (!isset($data['rates'])) {
                throw new ExchangeRateException("Invalid response from API.");
            }

            $this->rates = $data['rates'];
        } catch (\Exception $e) {
            throw new ExchangeRateException("Failed to fetch exchange rates.");
        }
    }

    public function getRates(): array
    {
        return $this->rates;
    }

    public function convertToEur(float $amount, string $currency): float
    {
        if ($currency === 'EUR') {
            return $amount;
        }

        if (!isset($this->rates[$currency])) {
            throw new ExchangeRateException("Exchange rate for $currency not found.");
        }

        return $amount / $this->rates[$currency];
    }

    public function convertFromEur(float $amount, string $currency): float
    {
        if ($currency === 'EUR') {
            return $amount;
        }

        if (!isset($this->rates[$currency])) {
            throw new ExchangeRateException("Exchange rate for $currency not found.");
        }

        return $amount * $this->rates[$currency];
    }
}
