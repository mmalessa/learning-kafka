FROM golang:1.22.1-bookworm

RUN apt-get update -qq -y \
    && apt-get install -y build-essential ca-certificates file make gcc \
        librdkafka-dev

#    bash git openssh autoconf automake libtool gettext gettext-dev make g++ gcc texinfo curl \
#    librdkafka-dev musl-dev mold cyrus-sasl-dev

RUN   mkdir /go/pkg
RUN   chmod a+rwx /go/pkg

ARG APP_USER_ID
RUN useradd -s /bin/sh -u ${APP_USER_ID} -m appuser
USER appuser

WORKDIR /go/src/app
