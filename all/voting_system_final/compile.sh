#!/bin/bash
# compile.sh - Script to compile the Voting System

# Define Java 17 home path (adjust if your installation path differs)
JAVA17_HOME="/usr/lib/jvm/java-17-openjdk-amd64"

# Check if Java 17 exists at the specified path
if [ ! -f "$JAVA17_HOME/bin/javac" ]; then
    echo "Error: Java 17 compiler not found at $JAVA17_HOME/bin/javac"
    echo "Please install OpenJDK 17 or edit this script to set the correct path."
    exit 1
fi

# Remove old class files (if any)
echo "Removing old class files from bin (if any)..."
rm -rf bin

# Create output directory
echo "Creating output directory: bin"
mkdir -p bin

# Compile Java source files
echo "Compiling Java source files from src/main/java using $JAVA17_HOME/bin/javac..."
"$JAVA17_HOME/bin/javac" -d bin src/main/java/com/example/votingsystem/*.java

# Check if compilation was successful
if [ $? -eq 0 ]; then
    echo "Compilation successful. Class files are in bin"
else
    echo "Compilation failed. Please check the error messages above."
    exit 1
fi
