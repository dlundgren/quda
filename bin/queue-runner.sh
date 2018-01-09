#!/bin/sh
#
# Quick cron job that ensures the queue is running appropriately
#
# I'd run this either every minute or every 5 minutes
#
# Alternately, if you have runit installed... just use that

_rootpath=$( dirname $( dirname $( readlink -f $0 ) ) )

. "${_rootpath}/.env"

case "$1" in
	start)
		WHOAMI=`whoami`
		COUNT=`ps -U ${WHOAMI} | grep queue:run | grep -c -v grep`

		if [ "$COUNT" -gt 0 ]; then
			exit 0
		fi

		echo -n "Starting queue worker..."
		nohup /usr/bin/env php "${_rootpath}/quda" queue:run >/dev/null 2>&1 &>- &
		if [ $? -eq 0 ]; then
			echo "OK"
		else
			echo "FAIL"
		fi
		;;
	stop)
		echo -n "Stopping queue worker..."
		kill -9 `cat "${_rootpath}/data/queue.pid"`
		if [ $? -eq 0 ]; then
			echo "OK"
		else
			echo "FAIL"
		fi
		;;
	restart)
		$0 stop
		$0 start
		;;
	*)
		echo "Usage: '${_rootpath}/bin/queue-runner.sh' {start|stop|restart}"
		exit 1
		;;
esac

exit 0