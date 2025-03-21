/**
 * Avatar Helper Utility
 * Provides consistent avatar initial display across the application
 */

const AvatarHelper = {
    /**
     * Get the initial for an avatar from a user's name
     * @param {string} name - The user's full name
     * @return {string} The formatted initial
     */
    getInitial: function(name) {
      // Return first character followed by a period if name exists and isn't empty
      return name && name.length > 0 ? `${name.charAt(0)}` : '?';
    },
    
    /**
     * Initialize all avatar elements on the page
     * @param {string} userSelector - CSS selector for current user avatars
     * @param {string} otherSelector - CSS selector for other avatars with data-name attribute
     * @param {string} currentUserName - The current user's name
     */
    initAvatars: function(userSelector, otherSelector, currentUserName) {
      // Set current user avatars
      document.querySelectorAll(userSelector).forEach(avatar => {
        avatar.textContent = this.getInitial(currentUserName);
      });
      
      // Set other avatars based on data-name attribute
      document.querySelectorAll(otherSelector).forEach(avatar => {
        const name = avatar.getAttribute('data-name');
        avatar.textContent = this.getInitial(name);
      });
    }
  };
  
  // Make it globally accessible
  window.AvatarHelper = AvatarHelper;