# History

Dramatar ("a more dramatic avatar") was a free dynamic-avatar web service created in **2010** by Rocket Full, Ltd. Co. It is no longer running; this repository is a modern PHP rewrite of its avatar builders, preserved for reference.

## What it was

Dramatar offered a new take on the standard blog and forum avatars of the time (Gravatar, Identicon, Wavatar). A site passed in a unique identifier, typically the MD5 of a commenter's email address (any uniquely identifying text worked), plus an avatar type and a size. Dramatar chose and stacked parts based on that hash, so the same person always got the same avatar. Results were cached to disk and served as PNGs. Sites could use Dramatar alone or alongside Gravatar, showing a person's Gravatar when they had one and a Dramatar otherwise.

The layered types offered "hundreds of billions" of combinations.

Original avatar URLs looked like `http://dramatar.com/a/{type}_{size}_{md5}.png`.

## Ways it was used

- **WordPress plugin** 
- **vBulletin plugin** 
- **Vanilla plugin**
- **API**: plug in a type, a size and a unique text value and receive an image.

## Avatar types the service offered

Anime, Rag Dolls, Spirograph, Abstract, Arc and Boxy at launch of the news posts below; Blockies appeared later in the site copy.
A "Smilies" type was also planned but never shipped. See [models.md](models.md) for how each is rebuilt here.

## Credits

- **Anime** part artwork: Pikangie (deviantART), per the Sept 4, 2011 site post.
- **Rag Doll** part artwork: Ashley West, per the Sept 1, 2011 site post.
- **Blockies** and **flower** artwork: no attribution survives in the original files.
