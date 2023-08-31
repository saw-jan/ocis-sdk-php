## ocis-sdk-php

PHP SDK for ownCloud Infinite Scale.

See [test-client](./test-client/) for usage/installation example.

### oCIS Server

`test-client` uses the custom client `sdk` which is not in the official [ocis](github.com/owncloud/ocis) repository. To use it, you need to build and server ocis from [saw-jan/ocis](github.com/saw-jan/ocis/tree/sdk-client).

```bash
git clone --single-branch -depth 1 -b sdk-client git@github.com:saw-jan/ocis.git
```

### Start Client

```bash
make serve
```

Client serves on [localhost:9000](http://localhost:9000)
