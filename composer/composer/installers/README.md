custom-directory-installers
===========================

It's a fork of the official [composer/installers](https://github.com/composer/installers) but use the dynamic paths system of [ideaconnect/composer-custom-directory](https://github.com/ideaconnect/composer-custom-directory) in that way they are compatible each others.

# How to use it

Decalre your `"composer/installers": "1.1.0"` as usually but add:

```json
"repositories": [
    {
      "url": "https://github.com/WPBP/installers",
      "type": "vcs"
    },
}
```

In that way the packages that require in their composer.json don't annoying you but the dynamic paths system of [ideaconnect/composer-custom-directory](https://github.com/ideaconnect/composer-custom-directory) will works also with them.

Enjoy!
