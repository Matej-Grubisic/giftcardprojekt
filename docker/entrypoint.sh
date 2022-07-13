function set_emulator_hosts {
   if [ -f /root/.config/firestore-emulators/firebase.json ]; then
      emulators_config="$(cat /root/.config/firestore-emulators/firebase.json)";
      if [ "$(jq 'has("emulators")' <(echo "$emulators_config"))" == "true" ]; then
         # Set host to 0.0.0.0 only for some emulators selected by user
         echo "Configuring hosts for selected emulators:"
         for emulator in "functions" "firestore" "database" "hosting" "pubsub" "ui"; do
            if [ "$(jq ".emulators|has(\""$emulator"\")" <(echo "$emulators_config"))" == "true" ]; then
               emulators_config="$(jq ".emulators."$emulator".host=\"0.0.0.0\"" <(echo "$emulators_config"))"
               echo "- Host for $emulator emulator set to 0.0.0.0"
            fi
         done
         echo -en "$emulators_config" > '/root/.config/firestore-emulators/firebase.json'
         echo "Saved configuration for emulator hosts"
      fi
   else
      echo -e "\n\e[1;33mWARNING:\e[m Failed to set host to 0.0.0.0, config file not found!"
   fi
} >&2

# Set working directory to /root/.config/firestore-emulators
cd /root/.config/firestore-emulators

# Make sure that firebase-data for import/export exists
if [ ! -d firebase-data ]; then
      mkdir firebase-data
fi

if [ -f firebase.json ] && [[ $(wc -l <firebase.json) -ge 4 ]]; then
   exec firebase emulators:start --import=./firebase-data --export-on-exit
else
   if [ ! -f firebase.json ]; then
      echo -e "\n\e[1;33mWARNING:\e[m You need to complete firebase authentication!"
      echo "To complete this process this run this command in your terminal:"
      echo -e "\e[32;48;5;235mdocker exec -it $(uname -n) sh -c \"cd /root/.config/firestore-emulators&&firebase login --no-localhost&&firebase init\"\e[m"
      echo "After authentication initialize firestorm in two steps by selecting this two options:"
      echo " - Emulators: Set up local emulators for Firebase products"
      echo " - Use an existing project"
      echo "And select test project."
      echo "After that select emulators that you need (e.g. Firestore Emulator)"
      echo "Select default port for emulator/s by pressing ENTER"
      echo "Enable Emulator UI on port 4000"
      echo -e "Waiting until you execute this\033[5m...\033[0m"
      while [ ! -f firebase.json ]; do
         sleep 1
      done
      echo -e "\e[32mFirebase authenticate!\e[m"
   fi
   if [[ ! $(wc -l <firebase.json) -ge 4 ]]; then
      echo -e "\n\e[1;33mWARNING:\e[m You failed to configure emulators in initialization step!"
      echo "Use this command to try again:"
      echo -e "\e[32;48;5;235mdocker exec -it $(uname -n) sh -c \"cd /root/.config/firestore-emulators&&firebase init emulators\"\e[m"
      echo -e "Waiting until you execute this\033[5m...\033[0m"
      while [[ ! $(wc -l <firebase.json) -ge 4 ]]; do
         sleep 1
      done
   fi
   sleep 1
   set_emulator_hosts
   echo "Starting firebase emulators..."
   exec firebase emulators:start --import=./firebase-data --export-on-exit
   echo -e "Firebase emulator started and configured to persist data in docker volume.\n\e[32mContianer is ready for use!\e[m"
fi
