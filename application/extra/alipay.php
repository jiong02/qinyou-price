<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/20
 * Time: 22:26
 */

return [
    //应用ID
    'app_id' => "2016071901636648",

    //商户私钥
    'merchant_private_key' => "MIIEowIBAAKCAQEAu82zX8BJIcPSuktP9EwW5tUa93kvnzv/uR2T7yMiyYT6Gt9JJYPMAUHao7VzXDCj+f2+pm4cEL6aEbS6ZmMTaMOH8/oPm7nyOPDR+COblkN8gr0fPMc+Y2UU+Xh5hNbxjv4GrGvR76tgmaI92lQApGNY4jUO8lo0lA+w+ZHkXAf5tZDl94276tq7vDyZjdiwOadSTcP+PpFvnARQyGcXkOIY5yz6vCj4T/kyn0nCkwxUlrf7tw/ztk5Gic3w6phxI92A4U7sJDOohECSACet8374zGXXGyp9cNXfjZgNYz+5ebkc+/o9srh2aNuq3jfYYWPpe5jMZH4FSWxSLGpQJQIDAQABAoIBAGdeSSSiyZ30EsDHQzLLzq8vDLC52yRh+dcCGLK/PB5/OsofrDsh19+5R4ZkESLlAtxOdelVIc11m4ezWgWQ8tXvCZ2YPY8RQellY6yYrMKAUsADKHZjlEtRD8JgNUKQrFRwLWwpzFuGkJz/V9wb8F6K8BlR6vAqBlaYbGhxjKe6KaRvtY3kMiAZr4Uu5X1xG5ZWfsMOACyJ3/rE8nM1R5WP+NPmIu7awdtVYUA/Wh0kLKGSCcc0QaHa24FucVfZUCQS6C0YJ+1MZTnCcuJRtEF6CXA2KUYBcxpTZaXstfYUAqhdTqC0J5zWEIf9k7iHlJmNpXWuXG7ppGXi+X0YuAECgYEA8J6p31b3zTETvq+b1Iu9f5DIHlmU5b3UebfKWzA9yx3EFERc95YfK29DiRKtHM/NQ8w5KQVW8bK42QDZZt9CPgtCNegxmUbqrBYITF2n0APHEk37aBHqTaqUn6WZFJCx8B8SbgfB78PKum+BiABM7bS27BwRL/isFTrG3No2NmUCgYEAx87JxcAG28n87YA73F5u0Ba3pq+QyH0IfxWki+CqVEO2hzEZUUr1abRuJk0wh89ojkfO2jyNZgI++k6wncHoSFlAiSRf/8Nr8Rasr24evATZ1NIa/pdll/y2jDPx1EvP3ijcAKr1wvC0hf6WvvSpP30jiyyhxPplh7Udtn+qNsECgYEA0vUoVek3pKysdPgdlUFWyKq06Pb9NlcyG+zo+v3Wj2fvax1srJzvgvMvsNOw9puxiQlZ6/8EdS+OJKM795cxypewWvbR1WJ5iJpgeCN8Z0GInSHFkz5xv9oYJ8fV6FPbzXxQeitO+tkbukzcsdIhoB5aabNJ1lcc+BfqFeMyuIkCgYByfzIqqp6Dhlz08D3dSxPvFIWK9CJgcR3UTV+sdELG5MKM9/rNFcpKF4XjVupPePAuUEHd10MjyHe0UjFtRXfJNbQAoqKMWrzZO6gbI1xjW9hD1152s+UY0kz9TKrwf70PTpS7oTwRyIN6IWja5jKyWhBrKVlOGjriKExtjvzIQQKBgAdYzNjzmw+PcxY/a5jEUn9adSgxkmaKEG3/DSWL4g38FlvFdwmyEuQL9orCW5DtHh7q5vqUh8xzXYXRhY8QMO7dKM+z1SrhLe1o5IpQs6qDdzoLW0PZq/R4fSqFiZLyt/2Ycjv4O08qWc6yB21X2qOHFZOzeEPsc95m8RsH3WkE",

    //支付宝公钥
    'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAiQbzUm+N5yAV3VgzLUJAB3swqAuuuT8P98r0+gwUnbsUMFRnk3XoS/GONVfz1RDMMIynImVkjGAZNELiICcqoKfje/p0IcQoDEZi+c3VttbD4cUoT9gpIEZ+jWXwqt58FIdUf50qnEhzWxIz8xyUIbBOdnqRKX57Vl8E17C0yIaNW/dwox7RE2WbDdzSDNv7Gk0p1/MUw/9tnE7i9PAcrlC6JUUzOJDx6utc/XAjf3podU8CUJvKTBRNHCBu20EzTh4KvNqkdA1LyD9RIbn/auQB7EtJORlx9OmeY8KLNGQ7vzZVlJ5E0aNdeh8666bYTM4Hx6I2mZXLOO4OzN4w4QIDAQAB",

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

    //最大查询重试次数
    'max_query_retry' => "10",

    //查询间隔
    'query_duration' => "2",
];