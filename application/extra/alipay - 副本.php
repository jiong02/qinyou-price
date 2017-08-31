<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/20
 * Time: 22:26
 */

return [
    //应用ID
    'app_id' => "2017081408192845",

    //商户私钥
    'merchant_private_key' => "MIIEpAIBAAKCAQEAqndCwooAhMVzgedtDj7vOoCiTvMsBZwIej93xkhTqgV0ktINKXlMNOztrDKDKMgnuTvR0nerq6l1EfnX3X447LU+q9jNeA16wPLSowCMNrFfgNexPeQKs/sgcmqjIGYDMcpSesPuXvR8v86cdZ/FRblRNv2Kr9jd/wQcYaiBe+Jxdib5vi+3O6BnlvcUnICjG19Pwmo8TR9DJLatIeCiC73AoZnmGEz4BhhQYngxb4/13mukNp1uXUjMWaHO3B9ycLlCBccMvWXxJCmylEqZANYwrql8OTT1XNgQNTCiQ/saWxTqH4SdNAY5POxP8UeA7GqAN/Bkn+7FTqfnw7pvmQIDAQABAoIBAC/HZ6XhSk3sjfiOJioFB8aNAXFBhUg5OMMTAP5JjXGtP8RbLac/QVXgRqmqKssGduPbWW81bZ+ayp50OcXyOABYuK0wAj8xAAQOy202nRZDVvJlAl/HiBx0Od739qsBDMoq3D1ep3Rj8IdOYNozIvbs/097dyDclzSAMXhJ46kWFLdoelh3D2D7f2Of49oJrELgnwOOe2wx3yrA4eWMt+WXI5i/mVhmM6BsrcpWeOiiyFbCszXJhd8Z5fXga70avnLbte5kdTqrQf9xFsKcxE+GlkYjrmCDOrWs17m7LV5Oue7myurlXK6lE+qdQxV3lQSMVsYIq3AyKaXZCz07AbUCgYEA38hih4zbqS7cq7yF36FxdDVqP/PSwubmt0y6f77cso4RA+lukRQq5+ZiHjBFfC3QFdwMQGd1mwHlf5fxKSowdNaBOO4YvLyZz00n/Fp/yYs6i0Y6ZvxfZmob2WGCCIHikF5qxGXKpBQqJHQTvHi1tS3t5FvUDn9J8kayThAZu+8CgYEAwwHbjmaoYw/jWcCdK4hh3UrRScfMNdbr4+2Am2VK2i17xovVnwTkVnd6y63FAp3xpIjYeIapV6zymCRz7mFxgyz0TpQdXS9vp3CHvI6d0wl51sLQGhIm8dCxzEnA7P6JiHH44Jc6y1fGwB/c+GOdF3WbkkCZzOV9JjRBAnyPpPcCgYEA3Ae1CT5wRJr1Ek7c+pNMcEyM2bCtTEGoHBZvUWeirFWPWV9N/Yvs2/LkCna/+2c4MCYaTcDsG6rzsk79KdJ3romyqP1CiWCPgwqEBFYfS1WADKzSg6wlSRePpl9/cUn0MKsFI3JKmqXRAeK3/Rpa33f3bg70JAT3+iWU77hY9TUCgYBTn9BPWKaNFJsiOf8sU+fjxdnKEev4ipnNvGOSP/XBag/SLNUGxEpG6iW2gmYhoSmmrShnRxgHiRrfM4KjjxXmcrixmcKd22G+I/uRWHdFsKKW/iPPikk5GY2lVVtuRhkkcfuQFEoaOSH58bAItG8BSGXWae2KeD8ayv2120SeMQKBgQCtWeG8+2z6TMxHzprJF8j9E5N3jLF14V0FOM+jqld6In7TofvAIKapqwD6oZietHNbqDej+xwIZQaMBuaJzWlvFRJ/rICXNO3yU0/Tj6qQ83yTgiXsYjcuFhgFXot5QgvHQqwJOSIyUOP+ukRnoVoc5WIoZzJEjOvUP1sk3FNvrQ==",
    //支付宝公钥
    'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0TKU9/AZEkd43xoC3VnXVzU/DTmzd5H00im1wvDKu+K8+NvJsl8M01dB4oFUKO/MitxA5syl80wEXDjwnBqR58Lc6OSiPoTyiWQIV1YIA87J/1sPI87pgLCzXb59526F5Pfi5b9LAXvZNpoJ6YDoxHRlwA4AyX7eu4EmoP3XJPGt80BfibwklPmXYayDUfJTyB9+jpGiJ9WjMFxNBPGs/pbouxsyoJ53Sf8kc3K2ebsMY9EPfXmF8yU+K/8E5kR5fL2nTsd37bpDz2XJBcvarSf8Mv4Lt683+pOUSKixcrOj3conAuSpgDf2tVwdj5mwwPcCMscddx/pc8IAjiTHRwIDAQAB",
    //异步通知地址,只有扫码支付预下单可用
    'notify_url' => "http://www.baidu.com",

    //支付宝网关
    'gateway_url' => "https://openapi.alipay.com/gateway.do",

    //签名方式,默认为RSA2(RSA2048)
    'sign_type' => "RSA2",

    //编码格式
    'charset' => "UTF-8",

    //调用的接口版本
    'version'=> "1.0",

    //接口返回格式
    'format' => "json",

    //应用授权TOKEN
    'app_auth_token' => "",

    //订单失效时长
    'timeout_express' => '2h',

    //最大查询重试次数
    'max_query_retry' => "10",

    //查询间隔
    'query_duration' => "2",
];