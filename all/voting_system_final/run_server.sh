#!/bin/bash
# run_server.sh - Script to run the Voting System Server

# Define Java 17 home path (adjust if your installation path differs)
JAVA17_HOME="/usr/lib/jvm/java-17-openjdk-amd64"

# Check if Java 17 exists at the specified path
if [ ! -f "$JAVA17_HOME/bin/java" ]; then
    echo "Error: Java 17 runtime not found at $JAVA17_HOME/bin/java"
    echo "Please install OpenJDK 17 or edit this script to set the correct path."
    exit 1
fi

# Check if class files exist
if [ ! -d "bin" ]; then
    echo "Error: Class files directory 'bin' not found. Run ./compile.sh first."
    exit 1
fi

# Run the server with explicit Java 17 path
echo "Starting Voting System Server..."
"$JAVA17_HOME/bin/java" \
    -cp "bin" \
    com.example.votingsystem.Server

# Check if server started successfully
if [ $? -ne 0 ]; then
    echo "Error: Failed to start Voting System Server. Check Java output above for details."
    exit 1
fi
