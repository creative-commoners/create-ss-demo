# create-ss-demo

[![Build Status](https://travis-ci.org/creative-commoners/create-ss-demo.svg?branch=master)](https://travis-ci.org/creative-commoners/create-ss-demo)

A command line utility that creates demo sites using the SilverStripe Platform API.

## Requirements

* PHP ^7.1
* Docker to create containers
* A Docker Hub account to store the container tags
* SilverStripe Platform stack permissions for the content snapshots
* [sspak](https://github.com/silverstripe/sspak) to create snapshots

## Installation

Install globally with Composer:

```
composer global require creative-commoners/create-ss-demo dev-master
```

Ensure the global Composer bin folder is in your system path.

## Configuration

Ensure you have logged into your Docker Hub account:

```
docker login
```

## Usage

You have a local environment ready to share with someone, and you want to publish it. The steps involved are:

* Build and publish a Docker container
* Create the demo instance

### Building the Docker container

You don't need to have Docker enabled for your project, this tool will copy the necessary Docker configuration files
into your project in order to build it into an image. You can then delete the files manually, or leave them there.
At some point this will be automated as well.

To build the container, run `create-ss-demo build` with the following arguments:

* `name`: The name for your image, e.g. "sprint-2019-01-02"
* `username`: Your Docker Hub username, e.g. "johnsmith"
* `version`: The version to tag the image with, e.g. `0.1`

```
create-ss-demo build sprint-2019-01-02 johnsmith 0.1
```

This will build a Docker image with the specified name, tag it as the specified version, and push it to Docker Hub
under the specified username.

### Create the demo instance

Once you have a Docker container, you can ask SilverStripe Platform to build a demo environment with it. You will also
need a [sspak](https://github.com/silverstripe/sspak) snapshot which will need to be uploaded to a SilverStripe
Platform stack via the "Snapshots" section. Once this is complete, you will need to obtain the numeric ID for it from
the "Snapshots" section.

To request a demo instance, run `create-ss-demo instantiate` with the following arguments:

* `site_name`: The site name used for the demo subdomain, e.g. `johnsmithsprint1` (cannot contain dashes etc)
* `image`: The Docker Hub image name and tag/version, e.g. "johnsmith/sprint-2019-01-02:01"
* `stack_name`: The SilverStripe platform stack code, e.g. `mystack`
* `snapshot_id`: The sspak snapshot ID from SilverStripe platform to use for content, e.g. 12846

```
create-ss-demo instantiate johnsmithsprint1 johnsmith/sprint-2019-01-02:0.1 mystack 12846
```

The SilverStripe Platform API will start processing your request, and the command will poll occasionally and provide
the command with status updates. Once complete, you will be given the demo site URL and username/password to log into
it. This can now be shared with other people.

**Note:** You will need to have necessary permissions on the specified SilverStripe Platform stack in order
to perform this action.

### Destroying demo sites (todo: write this command)

Once your demo environment is no longer required you should close it down to prevent wasting AWS resources. To do this
you can run `create-ss-demo destroy` with the following arguments:

* `demo_id`: The demo site ID to destroy

**Note:** You will need to have necessary permissions on the demo's original SilverStripe Platform stack in order
to perform this action.
