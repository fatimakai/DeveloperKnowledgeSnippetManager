#!/bin/bash
# Performance testing script

echo "=== Performance Test with Octane ==="
echo ""

echo "Testing Homepage (/)"
for i in {1..5}; do
  echo -n "Request $i: "
  curl -o /dev/null -s -w "%{time_total}s\n" http://127.0.0.1/
done

echo ""
echo "Testing Login Page (/login)"
for i in {1..5}; do
  echo -n "Request $i: "
  curl -o /dev/null -s -w "%{time_total}s\n" http://127.0.0.1/login
done

echo ""
echo "Testing Health Endpoint (/up)"
for i in {1..3}; do
  echo -n "Request $i: "
  curl -o /dev/null -s -w "%{time_total}s\n" http://127.0.0.1/up
done

echo ""
echo "=== Concurrent Requests Test ==="
echo "Sending 5 simultaneous requests..."
{
  curl -o /dev/null -s -w "Request 1: %{time_total}s\n" http://127.0.0.1/ &
  curl -o /dev/null -s -w "Request 2: %{time_total}s\n" http://127.0.0.1/ &
  curl -o /dev/null -s -w "Request 3: %{time_total}s\n" http://127.0.0.1/ &
  curl -o /dev/null -s -w "Request 4: %{time_total}s\n" http://127.0.0.1/ &
  curl -o /dev/null -s -w "Request 5: %{time_total}s\n" http://127.0.0.1/ &
  wait
}

echo ""
echo "=== Test Complete ==="
