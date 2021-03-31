## Motivation
Magento uses special cookie called `X-Magento-Vary` to distinguish between different variants of some pages (eg. PDP page for customers with special discount).

When varnish server is used this is handled by adding content of `X-Magento-Vary` cookie to hash key.

This can be abused to bypass varnish page cache and generate high load on php server by generating random value for every request.

This extension provide a way to verify valid cookie on varnish server by providing extra cookie called `X-Magento-Vary-Sign` containing sha1 hash of `X-Magento-Vary` cookie content and signing key (which should be random value).
Without knowing secret, attacker isn't able to generate correctly signed cookie and we can verify it on varnish server and ignore incorrect values, therefore reuse cached page.

## Magento configuration
Edit the /app/etc/env.php file to configure the signing key.

```
...
     'vary_cookie_sign' => [
            'key' => 'REPLACE_THIS_WITH_SIGNING_KEY'
     ]
...
```

## Varnish configuration
You need to install [uplex vmod_blobdigest](https://code.uplex.de/uplex-varnish/libvmod-blobdigest) also available as RPM in [mageops repository](https://mageops.github.io/packages-rpm/).

Make sure you have those imports at the beginning of your VCL:
```
import blobdigest;
import cookie;
import blob;
```

Add to `sub vcl_init`
```
new sha1 =  blobdigest.digest(SHA1);
```

Add to `sub vcl_recv`
```
if (req.http.cookie ~ "X-Magento-Vary=") {
    cookie.parse(req.http.cookie);
    if(! sha1.update( blob.decode( encoded=cookie.get("X-Magento-Vary") + "REPLACE_THIS_WITH_SIGNING_KEY" ) ) ) {
         return (synth(500, "Internal Server Error"));
    }

    if ( blob.encode( encoding=HEX, case=LOWER, blob=sha1.final() ) != cookie.get("X-Magento-Vary-Sign") ) {
         cookie.delete("X-Magento-Vary");
         set req.http.cookie = cookie.get_string();
    }
}
```
NOTE: First if statement can only fail when `update` is called after `finish`, but this is not possible, however VCL do not allow calling object methods, therefore this function mainly as workaround to this limitation.

Do not forget to replace `REPLACE_THIS_WITH_SIGNING_KEY` with your unique random string, and make sure you use the same value in varnish and magento.
