//resources\js\Components\ProfilePictureOnChat.jsx

import React from 'react';

export default function ProfilePictureOnChat({ user }) {
  return (
    <>
      <div className="inline-block relative">
        <span className="inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-700">
          <span className="font-medium leading-none text-white">
            {user.name.charAt(0).toUpperCase()}
          </span>
        </span>
      </div>
    </>
  )
}
