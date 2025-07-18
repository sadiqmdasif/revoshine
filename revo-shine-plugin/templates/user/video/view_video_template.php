<div class="revo-profile-container">
    <div class="revo-flex-container">
        <div class="revo-profile-picture">
            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb" alt="Image Profile">
        </div>

        <div class="revo-profile-info">
            <span class="revo-profile-name">{user_name}</span>
            <span class="revo-profile-role">{roles}</span>
        </div>
    </div>

    <div class="revo-post-container">
        <div class="revo-video-container">
            <video class="revo-my-video" controls autoplay>
                <source src="{post_video}" type="video/mp4">
                <source src="{post_video}" type="video/avi">
                <source src="{post_video}" type="video/mkv">
                <source src="{post_video}" type="video/mov">
            </video>
        </div>

        <div class="revo-wrap-desc">
            <div class="revo-post-details">
                <div class="revo-post-title">{post_title}</div>
                <div class="revo-post-description">
                    {description}
                </div>
            </div>

            <div class="revo-post-actions">
                <div style="display: flex; flex-direction: column; align-items: center; color: #f97316;">
                    <i class="revo-iconsax iconsax" icon-name="eye"></i>
                    <p class="revo-action-count" style="color: #171717;">{views}</p>
                </div>
                <div style="display: flex; flex-direction: column; align-items: center; color: #ef4444;">
                    <i class="revo-iconsax iconsax" icon-name="heart"></i>
                    <p class="revo-action-count" style="color: #171717;">{likes}</p>
                </div>
            </div>
        </div>
        
    </div>
</div>