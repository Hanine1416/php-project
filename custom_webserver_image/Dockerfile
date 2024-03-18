FROM nginx:alpine

# Add debug statements using the shell form of the RUN command
RUN sh -c 'echo "Debugging: Listing files in current directory" && ls -l'

# Copy custom_hosts file to /etc/hosts
COPY ./custom_hosts /etc/hosts

# Add another debug statement
RUN sh -c 'echo "Debugging: Contents of /etc/hosts after copying" && cat /etc/hosts'
