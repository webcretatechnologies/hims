<?php

namespace Hims\Testimonial\Api\Data;

interface GridInterface
{

    const TestimonialId = 'testimonial_id';
    const Status = 'status';
    const Date = 'date';
    const UpdateTime = 'update_time';
    const Name = 'name';
    const Email = 'email';
    const Message='message';
    // const Youtube='youtube';
    // const Twitter='twitter';
    // const Facebook='facebook';
    const Image='image';
    const Rating='rating';
    const Store='store';



    public function getTestimonialId();

    public function setTestimonialId($testimonial_id);

    public function getStatus();

    public function setStatus($status);

    public function getDate();

    public function setDate($date);

    public function getUpdateTime();

    public function setUpdateTime($update_time);

    public function getName();

    public function setName($name);

    public function getEmail();

    public function setEmail($email);

    public function getMessage();

    public function setMessage($message);

    // public function getYoutube();

    // public function setYoutube($youtube);

    // public function getTwitter();

    // public function setTwitter($twitter);

    // public function getFacebook();

    // public function setFacebook($facebook);

    public function getImage();

    public function setImage($image);

    public function getRating();

    public function setRating($rating);

    public function getStore();

    public function setStore($store);

}
